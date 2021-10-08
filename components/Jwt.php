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

    /**
     * @var \Lcobucci\JWT\Token|null
     */
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

    /**
     * Issues a plain token
     *
     * @return \Lcobucci\JWT\Token\Plain
     */
    public function issue(array $claims, bool $remember = false)
    {
        $now = new DateTimeImmutable();

        $builder = $this->config->builder()
                                ->issuedBy('http://example.com')
                                ->issuedAt($now)
                                ->canOnlyBeUsedAfter($now)
                                ->expiresAt($now->modify($remember ? '+1 month' : '+1 hour'));

        foreach ($claims as $name => $value) {
            $builder = $builder->withClaim($name, $value);
        }

        $this->token = $builder->getToken($this->config->signer(), $this->config->signingKey());

        return $this->token;
    }

    public function setToken(string $token)
    {
        $this->token = $this->parse($token);
    }

    /**
     *
     * @return \Lcobucci\JWT\Token
     */
    private function parse(string $token)
    {
        return $this->config->parser()->parse($token);
    }

    public function getClaim(string $claim)
    {
        /** @var \Lcobucci\JWT\Token\DataSet */
        $dataSet = $this->token->claims();

        return $dataSet->get($claim);
    }

    public function validate(string $token)
    {
        $parsedToken = $this->parse($token);
        $constraints = $this->constraints;

        $this->config->validator()->assert($parsedToken, ...$constraints);
    }
}
