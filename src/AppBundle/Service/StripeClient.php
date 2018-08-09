<?php
namespace AppBundle\Service;
use AppBundle\Entity\Proposer;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Stripe\Charge;
use Stripe\Error\Base;
use Stripe\Stripe;
class StripeClient
{
    private $config;
    private $em;
    private $logger;
    public function __construct($secretKey, array $config, EntityManagerInterface $em, LoggerInterface $logger)
    {
        \Stripe\Stripe::setApiKey($secretKey);
        $this->config = $config;
        $this->em = $em;
        $this->logger = $logger;
    }
    public function createPremiumCharge(User $user, $token, $amount, Proposer $proposer, $nbrCredit)
    {
        try {
            $customer = \Stripe\Customer::create(array(
                'email' => $user->getEmail(),
                'card'  => $token
            ));

            $charge = \Stripe\Charge::create([
                'customer' => $customer->id,
                'amount' => $this->config['decimal'] ? $amount * 100 : $amount,
                'currency' => $this->config['currency'],
                'description' => $nbrCredit.' JobNow credits',

                'receipt_email' => $user->getEmail(),
            ]);
        } catch (\Stripe\Error\Base $e) {
            $this->logger->error(sprintf('%s exception encountered when creating a credit payment: "%s"', get_class($e), $e->getMessage()), ['exception' => $e]);
            throw $e;
        }
        $user->setChargeId($charge->id);

        $credit = $proposer->getCredit();

        $credit += $nbrCredit;

        $proposer->setCredit($credit);

        $this->em->flush();
    }
}