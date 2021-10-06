<?php

namespace app\components;

use DateTimeImmutable;
use InvalidArgumentException;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Yii;
use yii\base\Component;

class Jwt extends Component
{
    /** @var Configuration */
    private $config;
    private $constraints = [];

    private $token = null;

    public function init()
    {
        $this->config = Configuration::forAsymmetricSigner(
            new Sha256(),
            InMemory::file(Yii::getAlias('@runtime') . '/key.pem'),
            InMemory::file(Yii::getAlias('@runtime') . '/public.pem')
        );

        $this->constraints = [
            new \Lcobucci\JWT\Validation\Constraint\LooseValidAt(
                new \Lcobucci\Clock\FrozenClock(new DateTimeImmutable(null))
            ),
        ];
    }

    public function issue()
    {
        $now = new DateTimeImmutable();

        $this->token = $this->config->builder()
                                ->issuedBy('http://example.com')
                                ->issuedAt($now)
                                ->canOnlyBeUsedAfter($now)
                                ->expiresAt($now->modify('+1 hour'))
                                ->getToken($this->config->signer(), $this->config->signingKey());

        return $this->token;
    }

    private function parse(string $token)
    {
        return $this->config->parser()->parse($token);
    }

    public function getClaim(string $claim)
    {
        $dataSet = $this->token->claims();

        if (!$dataSet->has($claim)) {
            throw new InvalidArgumentException("Incorrect claim provided");
        }

        return $dataSet->get($claim);
    }

    public function validate(string $token)
    {
        $parsedToken = $this->parse($token);
        $constraints = $this->constraints;

        $this->config->validator()->assert($parsedToken, ...$constraints);
    }
}
