<?php

namespace AdminBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Trt\SwiftCssInlinerBundle\Plugin\CssInlinerPlugin;

class AdminController extends Controller
{

    public function indexAction()
    {
        $user = $this->getUser();

        if(!(isset($user) and in_array('ROLE_ADMIN', $user->getRoles()))){
            return $this->redirectToRoute('cproject_home');
        }

        $proposerRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Proposer')
        ;
        $proposerCount = $proposerRepository->countTotalDifferentProposer();

        $offerRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Offer')
        ;

        $totalActiveOffer = $offerRepository->countTotalActiveOffer();
        return $this->render('AdminBundle::index.html.twig',array(
            'totalActiveOffer' => $totalActiveOffer,
            'countProposer' => $proposerCount
        ));
    }

    public function listProposerAction(){

        $user = $this->getUser();

        if(!(isset($user) and in_array('ROLE_ADMIN', $user->getRoles()))){
            return $this->redirectToRoute('cproject_home');
        }

        $repository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:User')
        ;
        $users = $repository->findAll();

        $proposers = [];
        foreach($users as $user)
        {
            if($user->getProposer() != NULL)
            {
                $proposers[] = $user;
            }
        }

        return $this->render('AdminBundle::listProposer.html.twig', array(
            'proposers' => $proposers,
        ));
    }

    public function listVoterAction(){

        $user = $this->getUser();

        if(!(isset($user) and in_array('ROLE_ADMIN', $user->getRoles()))){
            return $this->redirectToRoute('cproject_home');
        }

        $voterRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Voter')
        ;
        $voters = $voterRepository->findAll();

        return $this->render('AdminBundle::listVoter.html.twig', array(
            'voters' => $voters
        ));
    }

    public function listAdminAction(){

        $user = $this->getUser();

        if(!(isset($user) and in_array('ROLE_ADMIN', $user->getRoles()))){
            return $this->redirectToRoute('cproject_home');
        }

        $userRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:User')
        ;
        $admins = $userRepository->getAdmins();

        return $this->render('AdminBundle::listAdmin.html.twig', array(
            'admins' => $admins
        ));
    }

    public function removeFromAdminAction(Request $request)
    {
        $elementId = $request->get('id');

        $user = $this->getUser();

        if(!(isset($user) and in_array('ROLE_ADMIN', $user->getRoles()))){
            return $this->redirectToRoute('cproject_home');
        }

        $userRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:User')
        ;
        $user = $userRepository->findOneBy(array('id' => $elementId));

        $em = $this->getDoctrine()->getManager();

        $user->removeRole('ROLE_ADMIN');

        $em->merge($user);
        $em->flush();

        return $this->redirectToRoute('list_admin');
    }

    public function promoteToAdminAction(Request $request)
    {
        $mail = $request->get('mail');

        $user = $this->getUser();

        if(!(isset($user) and in_array('ROLE_ADMIN', $user->getRoles()))){
            return $this->redirectToRoute('cproject_home');
        }

        $userRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:User')
        ;
        $user = $userRepository->findOneBy(array('username' => $mail));

        $em = $this->getDoctrine()->getManager();

        $user->addRole('ROLE_ADMIN');

        $em->merge($user);
        $em->flush();

        return $this->redirectToRoute('list_admin');
    }

    public function listOfferAction(Request $request){

        $archived = $request->get('archived');
        $archived = isset($archived)?$archived:0;
        $active = $request->get('active');
        $active = isset($active)?$active:0;
        $validated = $request->get('validated');

        $user = $this->getUser();

        if(!(isset($user) and in_array('ROLE_ADMIN', $user->getRoles()))){
            return $this->redirectToRoute('cproject_home');
        }

        $offerRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Offer')
        ;
        $arraySearch = array('archived' => $archived);

        if(isset($validated)){
            $arraySearch['validated'] = null;
        }

        if(isset($active) and $active){
            $arraySearch['validated'] = 1;
        }

        $offers = $offerRepository->findBy($arraySearch, array('creationDate' => 'DESC'));

        $voteRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Vote')
        ;
        $countArray = array();
        foreach ($offers as $offer){
            $countArray[$offer->getId()] = $voteRepository->countVoteOffer($offer);
        }

        $totalActiveOffer = $offerRepository->countTotalActiveOffer();
        $totalNotValidatedActiveOffer = $offerRepository->countTotalNotValidatedActiveOffer();

        return $this->render('AdminBundle::listOffer.html.twig', array(
            'offers' => $offers,
            'active' => $active,
            'archived' => $archived,
            'validated' => $validated,
            'totalActiveOffer' => $totalActiveOffer,
            'totalNotValidatedActiveOffer' => $totalNotValidatedActiveOffer,
            'countArray' => $countArray
        ));
    }

    public function changeValidationStatusAction(Request $request){
        $id = $request->get('id');
        $status = $request->get('status');
        $message = $request->get('message');
        $fromPrice = $request->get('fromPrice');
        $toPrice = $request->get('toPrice');

        $user = $this->getUser();

        if(!(isset($user) and in_array('ROLE_ADMIN', $user->getRoles()))){
            return $this->redirectToRoute('cproject_home');
        }

        $offerRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Offer')
        ;
        $offer = $offerRepository->findOneBy(array('id' => $id));

        $offer->setValidated($status);

        if($status == 1){
            $offer->setFromPrice($fromPrice);
            $offer->setToPrice($toPrice);
            $now = new \dateTime("midnight");
            $offer->setActivationDate($now);
        }

        $em = $this->getDoctrine()->getManager();

        $em->merge($offer);
        $em->flush();

        if(!$status){
            $subject = 'form.offer.invalid.subject';
            $view = 'AppBundle:Emails:offerInvalid.html.twig';
        }else{
            $subject = 'form.offer.valid.subject';
            $view = 'AppBundle:Emails:offerValid.html.twig';
        }

        $mailer = $this->container->get('swiftmailer.mailer');
        $translated = $this->get('translator')->trans($subject);
        $message = (new \Swift_Message($translated . ' ' . $translated = $this->get('translator')->trans($offer->getType()) . ' ' . $offer->getTown()))
            ->setFrom('cprojectlu@noreply.lu')
            ->setTo($offer->getProposer()->getUser()->getEmail())
            ->setBody(
                $this->renderView(
                    $view,
                    array('offer' => $offer,
                        'message' => $message,
                        'fromPrice' => $fromPrice,
                        'toPrice' => $toPrice,
                    )
                ),
                'text/html'
            )
        ;

        $message->getHeaders()->addTextHeader(
            CssInlinerPlugin::CSS_HEADER_KEY_AUTODETECT
        );
        $mailer->send($message);

        return $this->redirectToRoute('list_offer_admin');
    }

    public function closeEstimationAction(Request $request){
        $id = $request->get('id');
        $finalPrice = $request->get('finalPrice');

        $user = $this->getUser();

        if(!(isset($user) and in_array('ROLE_ADMIN', $user->getRoles()))){
            return $this->redirectToRoute('cproject_home');
        }

        $offerRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Offer')
        ;
        $offer = $offerRepository->findOneBy(array('id' => $id));

        $offer->setFinalPrice($finalPrice);

        $em = $this->getDoctrine()->getManager();

        $em->merge($offer);
        $em->flush();

        $mailer = $this->container->get('swiftmailer.mailer');
        $translated = $this->get('translator')->trans('email.closed.subject');
        $messageProposer = (new \Swift_Message($translated . ' ' . $this->get('translator')->trans($offer->getType()) . ' ' . $offer->getTown()))
            ->setFrom('cprojectlu@noreply.lu')
            ->setTo('arthur.regnault@altea.lu')
            ->setBody(
                $this->renderView(
                    'AppBundle:Emails:offerClosedProposer.html.twig',
                    array('offer' => $offer,
                        'finalPrice' => $finalPrice,
                    )
                ),
                'text/html'
            )
        ;

        $messageProposer->getHeaders()->addTextHeader(
            CssInlinerPlugin::CSS_HEADER_KEY_AUTODETECT
        );
        $mailer->send($messageProposer);

        $voteRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Vote')
        ;
        $votes = $voteRepository->findBy(array('offer' => $offer));

        foreach ($votes as $vote){
            $messageVoter = (new \Swift_Message($translated . ' ' . $this->get('translator')->trans($offer->getType()) . ' ' . $offer->getTown()))
                ->setFrom('cprojectlu@noreply.lu')
                ->setTo('arthur.regnault@altea.lu')
                ->setBody(
                    $this->renderView(
                        'AppBundle:Emails:offerClosedVoter.html.twig',
                        array('offer' => $offer,
                            'finalPrice' => $finalPrice,
                        )
                    ),
                    'text/html'
                )
            ;

            $messageVoter->getHeaders()->addTextHeader(
                CssInlinerPlugin::CSS_HEADER_KEY_AUTODETECT
            );

            $mailer->send($messageVoter);
        }



        return $this->redirectToRoute('list_offer_admin');
    }

    public function changeEstimationAction(Request $request){
        $id = $request->get('id');
        $finalPrice = $request->get('finalPrice');

        $user = $this->getUser();

        if(!(isset($user) and in_array('ROLE_ADMIN', $user->getRoles()))){
            return $this->redirectToRoute('cproject_home');
        }

        $offerRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Offer')
        ;
        $offer = $offerRepository->findOneBy(array('id' => $id));

        $offer->setFinalPrice($finalPrice);

        $em = $this->getDoctrine()->getManager();

        $em->merge($offer);
        $em->flush();

        return $this->redirectToRoute('list_offer_admin');
    }

    public function listVotePageAction(Request $request){
        $id = $request->get('id');

        $session = $request->getSession();

        $user = $this->getUser();

        if(!(isset($user) and in_array('ROLE_ADMIN', $user->getRoles()))){
            return $this->redirectToRoute('cproject_home');
        }

        $offerRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Offer')
        ;
        $offer = $offerRepository->findOneBy(array('id' => $id));

        $voteRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Vote')
        ;

        $votes = $voteRepository->findBy(array('offer' => $offer));

        $averageValue = $voteRepository->getAverageValue($offer);

        $voteArray = array();
        $sortingArray = array();

        foreach ($votes as $vote){
            $sortingArray[] = $vote->getEstimation();
            $voteArray[$vote->getEstimation()][] = $vote;
        }

        $closest = null;

        $finaPrice = $offer->getFinalPrice();

        foreach ($sortingArray as $item) {
            if ($closest === null || abs($finaPrice - $closest) > abs($item - $finaPrice)) {
                $closest = $item;
            }
        }

        $winners = $voteArray[$closest];


        return $this->render('AdminBundle::votePage.html.twig', array(
            'votes' => $votes,
            'offer' => $offer,
            'winners' => $winners,
            'averageValue' => $averageValue
        ));
    }

    public function logPageAction(Request $request)
    {
        $now = new \DateTime();
        $year = $request->get('year');
        $year = isset($year)?$year:$now->format('Y');
        $user = $this->getUser();

        if(!(isset($user) and in_array('ROLE_ADMIN', $user->getRoles()))){
            return $this->redirectToRoute('cproject_home');
        }

        $logRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:ActiveLog');

        $proposerRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Proposer');

        $voterRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Voter');

        $logCreditRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:LogCredit');

        $finalActiveLog = array();
        $finalVoterLog = array();
        $finalProposerLog = array();
        $finalCreditLog = array();
        $monthlyCreditLog = array();

        for ($i = 1; $i <= 12; $i++){
            $startDate = new \DateTime();
            $startDate->setDate($year, $i, 1);
            $dateToTest = $year."-".$i."-01";
            $lastDay = date('t',strtotime($dateToTest));
            $endDate= new \DateTime();
            $endDate->setDate($year, $i, $lastDay);
            $finalActiveLog[] = (int)$logRepository->countActiveBetween($startDate,$endDate)[0]['total'];
            $finalVoterLog[] = (int)$voterRepository->countActiveBetween($endDate)[0]['total'];
            $finalProposerLog[] =(int)$proposerRepository->countActiveBetween($endDate)[0]['total'];
            $finalCreditLog[] =(int)$logCreditRepository->countTotalBefore($endDate)[0]['total'];
            $monthlyCreditLog[] = (int)$logCreditRepository->countTotalMonthly($i, $year)[0]['total'];
        }

        return $this->render('AdminBundle::logPage.html.twig',array(
            'activeOfferLog' => $finalActiveLog,
            'activeProposerLog' => $finalProposerLog,
            'activeVoterLog' => $finalVoterLog,
            'creditLog' => $finalCreditLog,
            'monthlyCreditLog' => $monthlyCreditLog,
            'year' => $year
        ));
    }

}