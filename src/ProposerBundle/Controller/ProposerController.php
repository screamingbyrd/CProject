<?php

namespace ProposerBundle\Controller;

use AppBundle\Entity\ActiveLog;
use AppBundle\Entity\Proposer;
use AppBundle\Entity\FeaturedProposer;
use AppBundle\Entity\FeaturedOffer;
use AppBundle\Entity\Slot;
use AppBundle\Form\ProposerType;
use AppBundle\Form\OfferType;
use Ivory\GoogleMap\Base\Coordinate;
use Ivory\GoogleMap\Overlay\Marker;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Ivory\GoogleMap\Map;
use Ivory\GoogleMap\Service\Geocoder\GeocoderService;
use Http\Adapter\Guzzle6\Client as GuzzleAdapter;
use Http\Message\MessageFactory\GuzzleMessageFactory;
use Ivory\GoogleMap\Service\Geocoder\Request\GeocoderAddressRequest;
use Ivory\GoogleMap\Overlay\InfoWindow;
use Symfony\Component\HttpFoundation\JsonResponse;
use Trt\SwiftCssInlinerBundle\Plugin\CssInlinerPlugin;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;



class ProposerController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('', array('name' => $name));
    }

    public function createAction(Request $request)
    {

        $postAnOffer = $request->get('postOffer');
        $session = $request->getSession();

        $proposer = new Proposer();

        $form = $this->get('form.factory')->create(ProposerType::class);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {

            $data = $form->getData();


            $userRegister = $this->get('app.user_register');
            $user = $userRegister->register($data->getEmail(),$data->getEmail(),$data->getPassword(),$data->getFirstName(),$data->getLastName(), 'ROLE_PROPOSER');

            if($user != false){
                // the user is now registered !

                $em = $this->getDoctrine()->getManager();

                $proposer->setPhone($data->getPhone());
                $proposer->addUser($user);
                $user->setProposer($proposer);

                $em->persist($user);
                $em->persist($proposer);
                $em->flush();

                $translated = $this->get('translator')->trans('form.registration.successProposer');
                $session->getFlashBag()->add('info', $translated);

                if(isset($postAnOffer) and $postAnOffer){
                    return $this->redirectToRoute('post_offer');
                }else{
                    return $this->redirectToRoute('edit_proposer');
                }
            }else{

                $translated = $this->get('translator')->trans('form.registration.error');
                $session->getFlashBag()->add('danger', $translated);

                return $this->redirectToRoute('cproject_home');
            }
        }
        return $this->render('ProposerBundle:Form:createProposer.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function editAction(Request $request ){
        $user = $this->getUser();

        $session = $request->getSession();
        $repository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Proposer')
        ;

        $idProposer = $request->get('id');
        $userRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:User')
        ;

        $proposer = $repository->findOneBy(array('id' => isset($idProposer)?$idProposer:$user->getProposer()));

        if(!((isset($user) and $user->getProposer() == $proposer) ||  (isset($user) and in_array('ROLE_ADMIN', $user->getRoles())))){
            $translated = $this->get('translator')->trans('redirect.proposer');
            $session->getFlashBag()->add('danger', $translated);
            return $this->redirectToRoute('create_proposer');
        }

        if(in_array('ROLE_ADMIN', $user->getRoles())){
            $user = $userRepository->findOneBy(array('proposer' => $proposer));
        }

        $session = $request->getSession();

        $proposer->setFirstName($user->getFirstName());
        $proposer->setLastName($user->getLastName());
        $proposer->setEmail($user->getEmail());

        $form = $this->get('form.factory')->create(ProposerType::class, $proposer);

        $form->remove('password');
        $form->remove('terms');

        // Si la requête est en POST
        if ($request->isMethod('POST')) {

            $form->handleRequest($request);
            if ($form->isValid()) {

                $data = $form->getData();

                $userManager = $this->get('fos_user.user_manager');

                $user->setUsername($data->getEmail());
                $user->setUsernameCanonical($data->getEmail());
                $user->setEmail($data->getEmail());
                $user->setEmailCanonical($data->getEmail());
                $user->setFirstName($data->getFirstName());
                $user->SetLastName($data->getLastName());
                $userManager->updateUser($user);

                $proposer->setPhone($data->getPhone());
                $proposer->addUser($user);

                $em = $this->getDoctrine()->getManager();
                $em->merge($proposer);
                $em->flush();

                $translated = $this->get('translator')->trans('form.registration.edited');
                $session->getFlashBag()->add('info', $translated);


                return $this->redirectToRoute('dashboard_proposer');

            }
        }

        return $this->render('ProposerBundle:Form:editProposer.html.twig', array(
            'form' => $form->createView(),
            'user' => $user,
        ));
    }

    public function deleteAction(Request $request, $id)
    {
        $user = $this->getUser();
        $session = $request->getSession();

        $repository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Proposer');

        $userRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:User');

        $offerRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Offer');

        $featuredProposerRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:FeaturedProposer');

        $proposer = $repository->findOneBy(array('id' => isset($id)?$id:$user->getProposer()));

        if(!((isset($user) and $user->getProposer() == $proposer) ||  (isset($user) and in_array('ROLE_ADMIN', $user->getRoles())))){
            $translated = $this->get('translator')->trans('redirect.proposer');
            $session->getFlashBag()->add('danger', $translated);
            return $this->redirectToRoute('create_proposer');
        }

        $userArray = $userRepository->findBy(array('proposer' => $proposer));

        $featuredProposerArray = $featuredProposerRepository->findBy(array('proposer' => $proposer));

        $offers = $offerRepository->findBy(array('proposer' => $proposer, 'archived' => false));

        $em = $this->getDoctrine()->getManager();

        $proposer->setPhone(null);
        $em->merge($proposer);

        foreach ($offers as $offer) {
            $offer->setArchived(true);
            $em->merge($offer);
        }

        foreach ($featuredProposerArray as $featuredProposer) {
            $featuredProposer->setArchived(true);
            $em->merge($featuredProposer);
        }
        $mailer = $this->container->get('swiftmailer.mailer');
        foreach ($userArray as $user){
            $mail = $user->getEmail();
            $em->remove($user);

            $translated = $this->get('translator')->trans('voter.delete.deleted');
            $message = (new \Swift_Message($translated))
                ->setFrom('cprojectlu@noreply.lu')
                ->setTo($mail)
                ->setBody(
                    $this->renderView(
                        'AppBundle:Emails:userDeleted.html.twig',
                        array()
                    ),
                    'text/html'
                )
            ;


            $message->getHeaders()->addTextHeader(
                CssInlinerPlugin::CSS_HEADER_KEY_AUTODETECT
            );
            $mailer->send($message);
        }

        $message = (new \Swift_Message($proposer->getName().' has archived his account'))
            ->setFrom('cprojectlu@noreply.lu')
            ->setTo('commercial@cproject.lu')
            ->setBody(
                $this->renderView(
                    'AppBundle:Emails:userDeleted.html.twig',
                    array()
                ),
                'text/html'
            )
        ;

        $message->getHeaders()->addTextHeader(
            CssInlinerPlugin::CSS_HEADER_KEY_AUTODETECT
        );

        $mailer->send($message);

        $em->flush();
        $translated = $this->get('translator')->trans('voter.delete.deleted');
        $session->getFlashBag()->add('info', $translated);

        return $this->redirectToRoute('cproject_home');

    }

    public function showAction($id)
    {
        $repository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Proposer');

        $proposer = $repository->find($id);

        $phone = $proposer->getPhone();
        if(!isset($phone)){
            return $this->redirectToRoute('cproject_home');
        }

        $offerRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Offer');


        $offers = $offerRepository->findBy(
            array('proposer' => $proposer, 'archived' => false),
            array('startDate' => 'DESC')
        );

        $arrayOffer = array();
        $generateUrlService = $this->get('app.offer_generate_url');

        foreach ($offers as $offer){
            if($offer->isActive()){

                $offer->setOfferUrl($generateUrlService->generateOfferUrl($offer));

                $arrayOffer[] = $offer;
            }
        }

        $tagArray  = $proposer->getTag();

        if(count($tagArray) == 0){
            $tagArray = $offerRepository->getOfferTags($proposer->getId());
        }

        $map = new Map();

        //workarround to ssl certificat pb curl error 60

        $config = [
            'verify' => false,
        ];

        $adapter = GuzzleAdapter::createWithConfig($config);

        // GeoCoder API
        $geocoder = new GeocoderService($adapter, new GuzzleMessageFactory());

        //try to match string location to get Object with lat long info
        if($proposer->getLocation()){
            $request = new GeocoderAddressRequest($proposer->getLocation());
        }else{
            $request = new GeocoderAddressRequest('228 Route d\'Esch, Luxembourg');
        }

        $response = $geocoder->geocode($request);


        $status = $response->getStatus();

        foreach ($response->getResults() as $result) {

            $coord = $result->getGeometry()->getLocation();
            continue;

        }

        if(isset($coord)) {
            $marker = new Marker($coord);
            $marker->setVariable('marker');
            $map->setCenter($coord);
            $map->getOverlayManager()->addMarker($marker);
        }

        $map->setStylesheetOption('width', 1100);
        $map->setStylesheetOption('min-height', 1100);
        $map->setMapOption('zoom', 10);


        return $this->render('ProposerBundle:Proposer:show.html.twig', array(
            'proposer' => $proposer,
            'map' => $map,
            'status' => $status,
            'offers' => $arrayOffer,
            'tags' => $tagArray
        ));

    }

    public function listAction(){
        $repository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Proposer');

        $proposers = $repository->findAll();

        return $this->render('ProposerBundle:Proposer:list.html.twig', array(
            'proposers' => $proposers
        ));
    }

    public function dashboardAction(Request $request){
        $user = $this->getUser();
        $session = $request->getSession();

        if(!isset($user) || !in_array('ROLE_PROPOSER', $user->getRoles())){
            $translated = $this->get('translator')->trans('redirect.proposer');
            $session->getFlashBag()->add('danger', $translated);
            return $this->redirectToRoute('create_proposer');
        }

        $repository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Proposer')
        ;
        $idProposer = $request->get('id');
        $userRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:User')
        ;

        $proposer = $repository->findOneBy(array('id' => isset($idProposer)?$idProposer:$user->getProposer()));
        $user = $userRepository->findOneBy(array('proposer' => $proposer));


        return $this->render('ProposerBundle::dashboard.html.twig', array(
            'proposer' => $proposer,
        ));
    }

    public function myOfferPageAction(Request $request, $archived = 0){
        $user = $this->getUser();
        $session = $request->getSession();

        if(!isset($user) || !in_array('ROLE_PROPOSER', $user->getRoles())){
            $translated = $this->get('translator')->trans('redirect.proposer');
            $session->getFlashBag()->add('danger', $translated);
            return $this->redirectToRoute('create_proposer');
        }

        $repository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Proposer')
        ;
        $idProposer = $request->get('id');
        $userRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:User')
        ;

        $proposer = $repository->findOneBy(array('id' => isset($idProposer)?$idProposer:$user->getProposer()));
        $user = $userRepository->findOneBy(array('proposer' => $proposer));

        $OfferRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Offer')
        ;

        $searchArray = array('proposer' => $user->getProposer());

        if($archived == 0){
            $searchArray['archived'] = 0;
        }

        $_SESSION['archived'] = $archived;

        $offers = $OfferRepository->findBy($searchArray);
        $generateUrlService = $this->get('app.offer_generate_url');
        foreach ($offers as &$offer){
            $offer->setOfferUrl($generateUrlService->generateOfferUrl($offer));
            $finalArray[$offer->getId()]['offer'] = $offer;
        }

        return $this->render('ProposerBundle::myOffers.html.twig', array(
            'offers' => $offers,
            'proposer' => $proposer,
        ));
    }

    public function featuredProposerPageAction(Request $request){
        $user = $this->getUser();

        $now = new \DateTime();
        $year = $request->get('year');
        $year = isset($year)?$year:$now->format('Y');

        $featuredProposerRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:FeaturedProposer')
        ;

        $featuredProposer = $featuredProposerRepository->findBy(array('archived' => 0));
        $featuredArray = array();

        foreach ($featuredProposer as $item) {

            $featuredArray[$item->getStartDate()->format('d/m/Y')]['id'][] = $item->getProposer()->getId();
            $featuredArray[$item->getStartDate()->format('d/m/Y')]['featured'][] = $item;
        }

        $creditInfo = $this->container->get('app.credit_info');
        $now = new \DateTime();
        $now->modify( '- 1 week' );

        return $this->render('ProposerBundle::featuredProposer.html.twig', array(
            'featuredProposerArray' => $featuredArray,
            'user' => $user,
            'featuredProposerCredit' => $creditInfo->getFeaturedProposer(),
            'now' => $now,
            'year' => $year
        ));
    }

    public function reserveFeaturedProposerAction(Request $request){
        $date = $request->get('date');
        $userId = $request->get('user');
        $session = $request->getSession();

        $user = $this->getUser();
        if(!isset($user) || !in_array('ROLE_PROPOSER', $user->getRoles())|| $user->getId() != (int)$userId){
            return $this->redirectToRoute('create_proposer');
        }

        $creditInfo = $this->container->get('app.credit_info');

        $proposer = $user->getProposer();

        $creditProposer = $proposer->getCredit();
        $creditFeaturedProposer = $creditInfo->getFeaturedProposer();

        if($creditProposer < $creditFeaturedProposer){
            $translated = $this->get('translator')->trans('form.offer.activate.error');
            $session->getFlashBag()->add('danger', $translated);
            return $this->redirectToRoute('cproject_credit');
        }

        $proposer->setCredit($creditProposer - $creditFeaturedProposer);

        $featuredProposer = new FeaturedProposer();
        $featuredProposer->setProposer($proposer);
        $startDate = new \DateTime($date['date']);
        $endDate = new \DateTime($date['date']);

        $featuredProposer->setStartDate($startDate);
        $featuredProposer->setEndDate($endDate->modify( '+ 1 week' ));
        $featuredProposer->setArchived(0);

        $em = $this->getDoctrine()->getManager();
        $em->persist($featuredProposer);
        $em->flush();

        return $this->redirectToRoute('featured_proposer_page', array('year' => substr($date['date'], 0, strpos($date['date'], '-'))));
    }

    public function deleteFeaturedProposerAction(Request $request){

        $session = $request->getSession();
        $featuredId = $request->get('id');
        $user = $this->getUser();

        if(!isset($user) || !in_array('ROLE_ADMIN', $user->getRoles())){
            return $this->redirectToRoute('create_voter');
        }

        $featuredProposerRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:FeaturedProposer')
        ;
        $featuredProposer = $featuredProposerRepository->findOneBy(array('id' => $featuredId));

        $featuredProposer->setArchived(true);

        $em = $this->getDoctrine()->getManager();
        $em->merge($featuredProposer);
        $em->flush();

        $session->getFlashBag()->add('info', 'featured proposer archived');

        return $this->redirectToRoute('featured_proposer_page');
    }

    public function featuredOfferPageAction(Request $request){
        $now = new \DateTime();
        $year = $request->get('year');
        $year = isset($year)?$year:$now->format('Y');
        $user = $this->getUser();
        $proposer = $user->getProposer();
        $featuredOfferRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:FeaturedOffer')
        ;
        $featuredOffer = $featuredOfferRepository->findBy(array('archived' => 0));
        $featuredArray = array();

        $offerRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Offer')
        ;

        $offers = $offerRepository->findBy(array('proposer' => $user->getProposer(), 'archived' => false));

        foreach ($featuredOffer as $item) {
            $featuredArray[$item->getStartDate()->format('d/m/Y')]['ids'][] = $item->getOffer()->getId();
            if($item->getOffer()->getProposer() == $proposer){
                $featuredArray[$item->getStartDate()->format('d/m/Y')]['features'][] = $item;
            }
        }

        $creditInfo = $this->container->get('app.credit_info');

        $now = new \DateTime();
        $now->modify( '- 1 week' );

        return $this->render('ProposerBundle::featuredOffer.html.twig', array(
            'featuredOfferArray' => $featuredArray,
            'user' => $user,
            'featuredOfferCredit' => $creditInfo->getFeaturedOffer(),
            'offers' => $offers,
            'now' => $now,
            'year' => $year
        ));
    }

    public function reserveFeaturedOfferAction(Request $request){
        $date = $request->get('date');
        $userId = $request->get('user');
        $offerId = $request->get('offerId');
        $session = $request->getSession();

        $user = $this->getUser();
        if(!isset($user) || !in_array('ROLE_PROPOSER', $user->getRoles())|| $user->getId() != (int)$userId){
            return $this->redirectToRoute('create_proposer');
        }

        $offerRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Offer')
        ;

        $offer = $offerRepository->findById($offerId);

        $creditInfo = $this->container->get('app.credit_info');

        $proposer = $user->getProposer();

        $creditProposer = $proposer->getCredit();
        $creditFeaturedOffer = $creditInfo->getFeaturedOffer();

        if($creditProposer < $creditFeaturedOffer){
            $translated = $this->get('translator')->trans('form.offer.activate.error');
            $session->getFlashBag()->add('danger', $translated);
            return $this->redirectToRoute('cproject_credit');
        }

        $proposer->setCredit($creditProposer - $creditFeaturedOffer);

        $featuredOffer = new FeaturedOffer();
        $featuredOffer->setOffer($offer[0]);
        $startDate = new \DateTime($date['date']);
        $endDate = new \DateTime($date['date']);

        $featuredOffer->setStartDate($startDate);
        $featuredOffer->setEndDate($endDate->modify( '+ 1 week' ));
        $featuredOffer->setArchived(0);

        $em = $this->getDoctrine()->getManager();
        $em->persist($featuredOffer);
        $em->flush();

        return $this->redirectToRoute('featured_offer_page', array('year' => substr($date['date'], 0, strpos($date['date'], '-'))));
    }

    public function deleteFeaturedOfferAction(Request $request){

        $session = $request->getSession();
        $featuredId = $request->get('id');
        $user = $this->getUser();

        if(!isset($user) || !in_array('ROLE_ADMIN', $user->getRoles())){
            return $this->redirectToRoute('create_voter');
        }

        $featuredOfferRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:FeaturedOffer')
        ;
        $featuredOffer = $featuredOfferRepository->findOneBy(array('id' => $featuredId));

        $featuredOffer->setArchived(true);

        $em = $this->getDoctrine()->getManager();
        $em->merge($featuredOffer);
        $em->flush();

        $session->getFlashBag()->add('info', 'featured offer deleted');

        return $this->redirectToRoute('featured_offer_page');
    }

    public function buySlotAction(Request $request){
        $session = $request->getSession();
        $user = $this->getUser();

        if(!isset($user) || !in_array('ROLE_PROPOSER', $user->getRoles())){
            return $this->redirectToRoute('create_proposer');
        }

        $proposer = $user->getProposer();

        $creditInfo = $this->container->get('app.credit_info');

        $creditProposer = $proposer->getCredit();
        $buySlot = $creditInfo->getBuySlot();

        if($creditProposer < $buySlot){
            $translated = $this->get('translator')->trans('form.offer.activate.error');
            $session->getFlashBag()->add('danger', $translated);
            return $this->redirectToRoute('cproject_credit');
        }

        $proposer->setCredit($creditProposer - $buySlot);

        $em = $this->getDoctrine()->getManager();
        $em->merge($proposer);
        $em->flush();

        $slot = new Slot();

        $slot->setProposer($proposer);
        $now =  new \DateTime();
        $next = new \DateTime();
        $slot->setStartDate($now);
        $slot->setEndDate($next->modify( '+ 1 year' ));

        $em->persist($slot);
        $em->flush();


        $translated = $this->get('translator')->trans('slot.buy.success');
        $session->getFlashBag()->add('info', $translated);

        return $this->redirectToRoute('proposer_offers', array('archived' => $_SESSION['archived']));
    }

    public function addToSlotAction(Request $request){
        $session = $request->getSession();
        $id = $request->get('id');
        $ajax = $request->get('ajax');
        $user = $this->getUser();

        if(!isset($user) || !in_array('ROLE_PROPOSER', $user->getRoles())){
            return $this->redirectToRoute('create_proposer');
        }

        $offerRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Offer')
        ;
        $currentOffer = $offerRepository->findOneBy(array('id' => $id));

        $slotRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Slot')
        ;
        $currentSlot = $slotRepository->getCurrentSlotProposer($user->getProposer()->getId());

        $em = $this->getDoctrine()->getManager();

        foreach ($currentSlot as $slot){
            $offer = $slot->getOffer();
            if(!isset($offer)){
                $now =  new \DateTime();
                $slot->setOffer($currentOffer);
                $currentOffer->setSlot($slot);
                $currentOffer->setUpdateDate($now);

                $activeLog = new ActiveLog();
                $activeLog->setOfferId($currentOffer->getId());
                $activeLog->setSlotId($slot->getId());
                $activeLog->setStartDate($now);

                $em->merge($slot);
                $em->merge($currentOffer);
                $em->merge($activeLog);
                $em->flush();

                $translated = $this->get('translator')->trans('slot.add.success');
                $session->getFlashBag()->add('info', $translated);

                return $this->redirectToRoute('proposer_offers', array('archived' => $_SESSION['archived']));
            }
        }

        $translated = $this->get('translator')->trans('slot.add.error');
        $session->getFlashBag()->add('danger', $translated);

        return $this->redirectToRoute('proposer_offers', array('archived' => $_SESSION['archived']));
    }

    public function removeFromSlotAction(Request $request){
        $session = $request->getSession();
        $id = $request->get('id');
        $user = $this->getUser();

        if(!isset($user) || !in_array('ROLE_PROPOSER', $user->getRoles())){
            $translated = $this->get('translator')->trans('redirect.proposer');
            $session->getFlashBag()->add('danger', $translated);
            return $this->redirectToRoute('create_proposer');
        }

        $offerRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Offer')
        ;
        $offer = $offerRepository->findOneBy(array('id' => $id));

        $slotRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Slot')
        ;
        $slot = $slotRepository->findOneBy(array('offer' => $offer));

        $em = $this->getDoctrine()->getManager();

        $slot->setOffer(null);
        $offer->setSlot(null);

        $activeLogRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:ActiveLog')
        ;
        $activeLog = $activeLogRepository->selectCurrentLog($offer->getId(), $slot->getId(), true);

        if(isset($activeLog) && !empty($activeLog)){
            $now = new \DateTime("midnight");
            $activeLog[0]->setEndDate($now);
            $em->merge($activeLog[0]);
        }

        $em->merge($slot);
        $em->merge($offer);
        $em->flush();

        $translated = $this->get('translator')->trans('slot.remove.success');
        $session->getFlashBag()->add('info', $translated);

        return $this->redirectToRoute('proposer_offers', array('archived' => $_SESSION['archived']));
    }

    public function EmptySlotAction(Request $request){
        $session = $request->getSession();
        $id = $request->get('id');
        $user = $this->getUser();

        if(!isset($user) || !in_array('ROLE_PROPOSER', $user->getRoles())){
            return $this->redirectToRoute('create_proposer');
        }

        $slotRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Slot')
        ;
        $slot = $slotRepository->findOneBy(array('id' => $id));

        $offerRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Offer')
        ;
        $offer = $offerRepository->findOneBy(array('slot' => $slot));

        $em = $this->getDoctrine()->getManager();

        $slot->setOffer(null);
        $offer->setSlot(null);

        $activeLogRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:ActiveLog')
        ;
        $activeLog = $activeLogRepository->selectCurrentLog($offer->getId(), $slot->getId(), true);

        if(isset($activeLog) && !empty($activeLog)){
            $now = new \DateTime("midnight");
            $activeLog[0]->setEndDate($now);
            $em->merge($activeLog[0]);
        }

        $em->merge($slot);
        $em->merge($offer);
        $em->flush();

        $translated = $this->get('translator')->trans('slot.empty.success');
        $session->getFlashBag()->add('info', $translated);

        return $this->redirectToRoute('proposer_offers', array('archived' => $_SESSION['archived']));
    }

    public function listAppliedVoterPageAction(Request $request){
        $id = $request->get('id');

        $session = $request->getSession();

        $user = $this->getUser();

        $proposer = $user->getProposer();

        $offerRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Offer')
        ;
        $offer = $offerRepository->findOneBy(array('id' => $id));

        $generateUrlService = $this->get('app.offer_generate_url');
        $offer->setOfferUrl($generateUrlService->generateOfferUrl($offer));

        if(!((isset($user) and in_array('ROLE_PROPOSER', $user->getRoles()) and $offer->getProposer()->getId() == $proposer->getId()) || in_array('ROLE_ADMIN', $user->getRoles()))){
            $translated = $this->get('translator')->trans('redirect.proposer');
            $session->getFlashBag()->add('danger', $translated);
            return $this->redirectToRoute('create_proposer');
        }

        $voteRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Vote')
        ;

        $votes = $voteRepository->findBy(array('offer' => $offer, 'archived' => 0));

        return $this->render('ProposerBundle::appliedVoterList.html.twig', array(
            'votes' => $votes,
            'offer' => $offer
        ));
    }

    public function sendSearchAction(Request $request, $id){
        $session = $request->getSession();
        $emloyerRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Proposer')
        ;
        $proposer = $emloyerRepository->findOneBy(array('id' => $id));
        $userRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:User')
        ;
        $user = $userRepository->findOneBy(array('proposer' => $proposer));

        $mail = $user->getEmail();

        $comment = $request->get('comment');
        $target_dir = "uploads/images/voter/";
        $target_file = $target_dir . md5(uniqid()) . basename($_FILES["cv"]["name"]);
        move_uploaded_file($_FILES["cv"]["tmp_name"], $target_file);

        $mailer = $this->container->get('swiftmailer.mailer');

        $message = (new \Swift_Message($this->get('translator')->trans('proposer.show.spontaenous.send')))
            ->setFrom('cprojectlu@noreply.lu')
            ->setTo($mail)
            ->setBody(
                $this->renderView(
                    'AppBundle:Emails:apply.html.twig',
                    array('comment' => $comment)
                ),
                'text/html'
            )
            ->attach(\Swift_Attachment::fromPath($target_file));
        ;

        $message->getHeaders()->addTextHeader(
            CssInlinerPlugin::CSS_HEADER_KEY_AUTODETECT
        );

        $mailer->send($message);
        unlink($target_file);

        $translated = $this->get('translator')->trans('proposer.show.spontaenous.sent');
        $session->getFlashBag()->add('info', $translated);
        return $this->redirectToRoute('show_proposer', array('id' => $id));
    }

}
