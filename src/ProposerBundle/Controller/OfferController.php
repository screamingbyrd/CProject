<?php
/**
 * Created by PhpStorm.
 * User: Altea IT
 * Date: 01/06/2018
 * Time: 12:10
 */

namespace ProposerBundle\Controller;

use AppBundle\Entity\ActiveLog;
use AppBundle\Entity\Proposer;
use AppBundle\Entity\Offer;
use AppBundle\Entity\Vote;
use AppBundle\Form\ProposerType;
use AppBundle\Form\OfferType;
use AppBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Ivory\GoogleMap\Map;
use Ivory\GoogleMap\Service\Geocoder\GeocoderService;
use Http\Adapter\Guzzle6\Client as GuzzleAdapter;
use Http\Message\MessageFactory\GuzzleMessageFactory;
use Ivory\GoogleMap\Service\Geocoder\Request\GeocoderAddressRequest;
use Ivory\GoogleMap\Overlay\InfoWindow;
use Ivory\GoogleMap\Base\Coordinate;
use Ivory\GoogleMap\Overlay\Marker;
use Ivory\GoogleMap\Event\Event;
use Ivory\GoogleMap\Place\Autocomplete;
use Ivory\GoogleMap\Place\AutocompleteType;
use Ivory\GoogleMap\Helper\Builder\PlaceAutocompleteHelperBuilder;
use Ivory\GoogleMap\Helper\Builder\ApiHelperBuilder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Trt\SwiftCssInlinerBundle\Plugin\CssInlinerPlugin;

class OfferController extends Controller
{

    public function postAction(Request $request)
    {

        $session = $request->getSession();

        $translator = $this->get('translator');

        $user = $this->getUser();

        $offer = new Offer();


        $form = $this->get('form.factory')->create(OfferType::class, $offer, array(
            'translator' => $translator,
        ));

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            $data = $form->getData();

            if(!isset($user)){
                $userRegister = $this->get('app.user_register');
                $user = $userRegister->register($data->getEmail(),$data->getEmail(),$data->getPassword(),$data->getFirstName(),$data->getLastName(), 'ROLE_PROPOSER');
            }

            $proposer = new Proposer();

            $em = $this->getDoctrine()->getManager();

            $proposer->setPhone($data->getPhone());
            $proposer->addUser($user);
            $user->setProposer($proposer);

            $em->persist($user);
            $em->persist($proposer);
            $em->flush();


            $offer->setProposer($user->getProposer());

            $past = new \DateTime('01-01-1900');
            $offer->setActivationDate($past);

            if ($offer->getImages()) {
                foreach ($offer->getImages() as $image) $image->setOffer($offer);
            }


            $em->persist($offer);
            $em->flush();
            $title = $this->get('translator')->trans($offer->getType()) . ' ' . $offer->getTown();

            $mailer = $this->container->get('swiftmailer.mailer');
            $translated = $this->get('translator')->trans('form.offer.created.subject');
            $message = (new \Swift_Message($translated . ' ' . $title))
                ->setFrom('cprojectlu@noreply.lu')
                ->setTo($user->getEmail())
                ->setBody(
                    $this->renderView(
                        'AppBundle:Emails:offerCreated.html.twig',
                        array('title' => $title,
                        )
                    ),
                    'text/html'
                )
            ;

            $message->getHeaders()->addTextHeader(
                CssInlinerPlugin::CSS_HEADER_KEY_AUTODETECT
            );
            $mailer->send($message);

            $translated = $this->get('translator')->trans('form.offer.creation.success');
            $session->getFlashBag()->add('info', $translated);

            return $this->redirectToRoute('proposer_offers');

        }
        return $this->render('ProposerBundle:Form:postOffer.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function editAction(Request $request)
    {

        $translator = $this->get('translator');

        $session = $request->getSession();

        $id = $request->get('id');

        $user = $this->getUser();

        $proposer = $user->getProposer();

        $offerRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Offer')
        ;
        $offer = $offerRepository->findOneBy(array('id' => $id));

        if(!((isset($user) and in_array('ROLE_PROPOSER', $user->getRoles()) and $offer->getProposer()->getId() == $proposer->getId()) || in_array('ROLE_ADMIN', $user->getRoles()))){
            $translated = $this->get('translator')->trans('redirect.proposer');
            $session->getFlashBag()->add('danger', $translated);
            return $this->redirectToRoute('create_proposer');
        }

        $form = $this->get('form.factory')->create(OfferType::class, $offer, array(
            'translator' => $translator,
        ));

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {

//            var_dump($form->getData()->getImages());exit;

            if ($offer->getImages()) {
                foreach ($offer->getImages() as $image) $image->setOffer($offer);
            }
            $em = $this->getDoctrine()->getManager();

            $em->merge($offer);
            $em->flush();

            $translated = $this->get('translator')->trans('form.offer.edition.success');
            $session->getFlashBag()->add('info', $translated);

            return $this->redirectToRoute('proposer_offers', array('archived' => $_SESSION['archived']));

        }
        return $this->render('ProposerBundle:Form:editOffer.html.twig', array(
            'form' => $form->createView(),
            'images' => $form->getData()->getImages()
        ));
    }

    public function deleteAction(Request $request){

        $session = $request->getSession();

        $id = $request->get('id');

        $ajax = $request->get('ajax');
        $user = $this->getUser();

        $proposerRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Proposer')
        ;
        $proposer = $proposerRepository->findOneBy(array('id' => $user->getProposer()));

        $offerRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Offer')
        ;

        $offer = $offerRepository->findOneBy(array('id' => $id));

        if(!((isset($user) and in_array('ROLE_PROPOSER', $user->getRoles()) and $offer->getProposer()->getId() == $proposer->getId()) || in_array('ROLE_ADMIN', $user->getRoles()))){
            $translated = $this->get('translator')->trans('form.offer.edition.error');
            $session->getFlashBag()->add('danger', $translated);
            return $this->redirectToRoute('dashboard_proposer', array('archived' => $_SESSION['archived']));
        }

        $bool = boolval($offer->isArchived());
        $offer->setArchived(!$bool);

        $em = $this->getDoctrine()->getManager();
        $em->merge($offer);

        $em->flush();

        if(!$bool == true){
            $voteRepository = $this
                ->getDoctrine()
                ->getManager()
                ->getRepository('AppBundle:Vote')
            ;

            $votes = $voteRepository->findBy(array('offer' => $offer));
            $mailer = $this->container->get('swiftmailer.mailer');
            $translated = $this->get('translator')->trans('email.archived.subject');
            foreach ($votes as $vote){
                $messageVoter = (new \Swift_Message($translated . ' ' . $this->get('translator')->trans($offer->getType()) . ' ' . $offer->getTown()))
                    ->setFrom('cprojectlu@noreply.lu')
                    ->setTo($vote->getVoter()->getUser()->getEmail())
                    ->setBody(
                        $this->renderView(
                            'AppBundle:Emails:offerArchived.html.twig',
                            array('offer' => $offer,
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

            $messageProposer = (new \Swift_Message($translated . ' ' . $this->get('translator')->trans($offer->getType()) . ' ' . $offer->getTown()))
                ->setFrom('cprojectlu@noreply.lu')
                ->setTo($offer->getProposer()->getUser()->getEmail())
                ->setBody(
                    $this->renderView(
                        'AppBundle:Emails:offerArchived.html.twig',
                        array('offer' => $offer,
                        )
                    ),
                    'text/html'
                )
            ;

            $messageProposer->getHeaders()->addTextHeader(
                CssInlinerPlugin::CSS_HEADER_KEY_AUTODETECT
            );

            $mailer->send($messageProposer);
        }

        $translated = $this->get('translator')->trans(!$bool?'form.offer.archived.success':'form.offer.unarchived.success');
        $session->getFlashBag()->add('info', $translated);

        $route = 'proposer_offers';

        if(in_array('ROLE_ADMIN', $user->getRoles())){
            $route = 'list_offer_admin';
        }

        if(isset($ajax) && $ajax){
            return new JsonResponse($this->generateUrl($route));
        }
        return $this->redirectToRoute($route);
    }

    public function eraseAction(Request $request){

        $session = $request->getSession();

        $id = $request->get('id');

        $user = $this->getUser();

        $proposerRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Proposer')
        ;
        $proposer = $proposerRepository->findOneBy(array('id' => $user->getProposer()));

        $offerRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Offer')
        ;

        $offer = $offerRepository->findOneBy(array('id' => $id));

        if(!((isset($user) and in_array('ROLE_PROPOSER', $user->getRoles()) and $offer->getProposer()->getId() == $proposer->getId()) || in_array('ROLE_ADMIN', $user->getRoles()))){
            $translated = $this->get('translator')->trans('form.offer.edition.error');
            $session->getFlashBag()->add('danger', $translated);
            return $this->redirectToRoute('dashboard_proposer', array('archived' => $_SESSION['archived']));
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($offer);

        $em->flush();

        $mailer = $this->container->get('swiftmailer.mailer');
        $translated = $this->get('translator')->trans('form.offer.deleted.subject');
        $message = (new \Swift_Message($translated . ' ' . $this->get('translator')->trans($offer->getType()) . ' ' . $offer->getTown()))
            ->setFrom('cprojectlu@noreply.lu')
            ->setTo($offer->getProposer()->getUser()->getEmail())
            ->setBody(
                $this->renderView(
                    'AppBundle:Emails:offerDeleted.html.twig',
                    array('title' => $this->get('translator')->trans($offer->getType()) . ' ' . $offer->getTown(),
                    )
                ),
                'text/html'
            )
        ;

        $message->getHeaders()->addTextHeader(
            CssInlinerPlugin::CSS_HEADER_KEY_AUTODETECT
        );
        $mailer->send($message);

        $voteRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Vote')
        ;

        $votes = $voteRepository->findBy(array('offer' => $offer));
        $translated = $this->get('translator')->trans('email.archived.subject');
        foreach ($votes as $vote){
            $messageVoter = (new \Swift_Message($translated . ' ' . $this->get('translator')->trans($offer->getType()) . ' ' . $offer->getTown()))
                ->setFrom('cprojectlu@noreply.lu')
                ->setTo($vote->getVoter()->getUser()->getEmail())
                ->setBody(
                    $this->renderView(
                        'AppBundle:Emails:offerArchived.html.twig',
                        array('offer' => $offer,
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

    public function showAction($id){
        $offerRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Offer')
        ;
        $offer = $offerRepository->findOneBy(array('id' => $id));

        $user = $this->getUser();

        if((!isset($user) || !in_array('ROLE_PROPOSER', $user->getRoles())) && ($offer->getArchived() == 1 || $offer->isValidated() === false)){
            return $this->redirectToRoute('offer_archived', array('id' => $id));
        }

        $voteRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Vote')
        ;
        $countVote = $voteRepository->countVoteOffer($offer);

        $averageValue = $voteRepository->getAverageValue($offer);

        $map = new Map();

        //workarround to ssl certificat pb curl error 60

        $config = [
            'verify' => false,
        ];

        $adapter = GuzzleAdapter::createWithConfig($config);

        // GeoCoder API
        $geocoder = new GeocoderService($adapter, new GuzzleMessageFactory());

        //try to match string location to get Object with lat long info
        if($offer->getLocation()){
            $request = new GeocoderAddressRequest($offer->getLocation());
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

        $remainingDays = 0;
        $activationDate = $offer->getActivationDate();
        $activated = $offer->isValidated();
        if(isset($activated) && $activated){
            $now = new \DateTime();
            date_modify($activationDate, '+2 week');
            $remainingDays = $activationDate->diff($now)->format("%a");
        }

        $finalPrice = $offer->getFinalPrice();

        $validated = $offer->getValidated();
        $statusOffer = $this->get('translator')->trans('offer.status.active');
        if($offer->isArchived()){
            $statusOffer = $this->get('translator')->trans('offer.archived');
        }elseif(!isset($validated)){
            $statusOffer = $this->get('translator')->trans('offer.status.standBy');
        }elseif (!$validated){
            $statusOffer = $this->get('translator')->trans('admin.offerList.invalid');
        }elseif (isset($finalPrice)){
            $statusOffer = $this->get('translator')->trans('offer.status.closed');
        }

        return $this->render('ProposerBundle:Offer:show.html.twig', array(
            'offer' => $offer,
            'map' => $map,
            'status' => $status,
            'countVote' => $countVote,
            'remainingDays' => $remainingDays,
            'averageValue' => $averageValue,
            'statusOffer' => $statusOffer
        ));
    }

    public function searchAction(Request $request){
        $keywords = $request->get('keyword');
        $location = $request->get('location');
        $proposer = $request->get('proposer');
        $tags = $request->get('tags');
        $type =  $request->get('type');
        $currentPage = $request->get('row');
        $numberOfItem =  $request->get('number');

        $searchParam = array(
            'keywords' => $keywords,
            'location' => $location,
            'proposer' => $proposer,
            'tags' => $tags,
            'type' => $type
        );
        $searchParam = json_encode($searchParam);

        $offerRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Offer')
        ;

        if(isset($location) && $location != ''){
            $data = $offerRepository->findByLocation($location);
        }
        elseif(preg_match("/[0-9]/",$keywords)){

            $data = $offerRepository->findBy(array('id'=>$keywords));
        }else{
            $data = $offerRepository->findBy(array('archived' => 0, 'validated' => 1), array('activationDate' => 'desc'));
        }

        $voteRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Vote')
        ;

        $generateUrlService = $this->get('app.offer_generate_url');

        $offerArray = array();

        $now = new \datetime();
        $now = $now->modify('-2 week');
        $comparaisonNow = new \datetime();
        foreach ($data as &$offer){
            $offer->setOfferUrl($generateUrlService->generateOfferUrl($offer));
            $offer->setCountVote($voteRepository->countVoteOffer($offer));
            $validated = $offer->isValidated();
            if((!isset($validated) || $validated)){
                $activationDate = $offer->getActivationDate();
                date_modify($activationDate, '+2 week');
                $remainingDays = $activationDate->diff($comparaisonNow)->format("%a");
                $offer->setRemainingDays($remainingDays);
                $offerArray[] = $offer;
            }
        }

        $countResult = count($offerArray);

//        $locationArray =array();
//        foreach ($data as $offer){
//            $locationArray[$offer->getLocation()][] = $offer;
//        }
//
//        $map = new Map();
//
//        //workarround to ssl certificat pb curl error 60
//
//        $config = [
//            'verify' => false,
//        ];
//
//        $adapter = GuzzleAdapter::createWithConfig($config);
//
//        // GeoCoder API
//        $geocoder = new GeocoderService($adapter, new GuzzleMessageFactory());
//        $markers = array();
//        $i = 1;
//
//        foreach ($locationArray as $location => $offers){
//
//            //try to match string location to get Object with lat long info
//            if($location){
//                $request = new GeocoderAddressRequest($location);
//            }else{
//                $request = new GeocoderAddressRequest('228 Route d\'Esch, Luxembourg');
//            }
//
//            $response = $geocoder->geocode($request);
//
//            $status = $response->getStatus();
//
//            foreach ($response->getResults() as $result) {
//
//                $coord = $result->getGeometry()->getLocation();
//                continue;
//
//            }
//
//            if(isset($coord)) {
//                $marker = new Marker($coord);
//
//                $marker->setVariable('marker' . $i);
//                $content = '<p class="map-offer-container">';
//                foreach ($offers as $offer){
//                    $content .=  '<a class="map-offer" href="'.$this->generateUrl('show_offer', array('id' => $offer->getId(),'url' => $this->generateOfferUrl($offer))).'">'.$offer->getTitle().'</a>';
//                }
//                $content .= '</p>';
//                $infoWindow = new InfoWindow($content);
//                $infoWindow->setAutoOpen(true);
//                $infoWindow->setAutoClose(true);
//                $infoWindow->setOption('maxWidth', 400);
//                $marker->setInfoWindow($infoWindow);
//
//                $markers[] = $marker;
//            }
//            $i++;
//        }
//        $map->getOverlayManager()->addMarkers($markers);
//
//        $event = new Event(
//            $map->getVariable(),
//            'zoom_changed',
//            'function(){'.
//            $marker->getVariable().'.setMap(null)'
//            .'}'
//        );
//        $map->getEventManager()->addEvent($event);
//        $map->setCenter($coord);
//        $map->setStylesheetOption('width', 1000);
//        $map->setStylesheetOption('min-height', 1100);
//        $map->setMapOption('zoom', 2);

        $adRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Ad')
        ;
        $ads = $adRepository->getCurrentAds();
        shuffle($ads);

        $finalArray = array_slice($offerArray, ($currentPage - 1 ) * $numberOfItem, $numberOfItem);

        $totalPage = ceil ($countResult / $numberOfItem);

        return $this->render('ProposerBundle:Offer:search-data.html.twig',
            array(
                'data' => $finalArray,
                'page' => $currentPage,
                'total' => $totalPage,
                'numberOfItem' =>($numberOfItem > $countResult? $countResult:$numberOfItem),
                'countResult' => $countResult,
                'searchParam' => $searchParam,
                'ads' => $ads
            )
        );
    }

    public function searchPageAction(Request $request){
        $keywords = $request->get('keyword');
        $location = $request->get('location');

        $autoComplete = new Autocomplete();
        $autoComplete->setInputId('place_input');

        $autoComplete->setInputAttributes(array(
            'class' => 'form-control',
            'name' => 'location',
            'placeholder' =>  $this->get('translator')->trans('form.offer.search.location')
        ));

        if(isset($location) && $location != ''){
            $autoComplete->setInputAttributes(array(
                'class' => 'form-control',
                'name' => 'location',
                'placeholder' =>  $this->get('translator')->trans('form.offer.search.location'),
                'value' => $location
            ));
        }

        $autoComplete->setTypes(array(AutocompleteType::CITIES));
        $autoCompleteHelperBuilder = new PlaceAutocompleteHelperBuilder();

        $autoCompleteHelper = $autoCompleteHelperBuilder->build();
        $apiHelperBuilder = ApiHelperBuilder::create();
        $apiHelperBuilder->setKey('AIzaSyBY8KoA6XgncXKSfDq7Ue7R2a1QWFSFxjc');
        $apiHelperBuilder->setLanguage($request->getLocale());

        $apiHelper = $apiHelperBuilder->build();

        return $this->render('ProposerBundle:Offer:searchPage.html.twig', array(
            'keyword' => $keywords,
            'autoComplete' => $autoCompleteHelper->render($autoComplete),
            'autoCompleteScript' => $apiHelper->render([$autoComplete])
        ));
    }

    public function voteAction(Request $request){
        $session = $request->getSession();

        $user = $this->getUser();

        $id = $request->get('id');
        $estimation = $request->get('estimation');
        $interested = $request->get('interested');

        if(!isset($user) || in_array('ROLE_PROPOSER', $user->getRoles())){
            return $this->redirectToRoute('create_voter', array('offerId' => $id));
        }

        $voterRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Voter')
        ;
        $voter = $voterRepository->findOneBy(array('user' => $user->getId()));

        $voterMail = $user->getEmail();

        $offerRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Offer')
        ;
        $offer = $offerRepository->findOneBy(array('id' => $id));

        $generateUrlService = $this->get('app.offer_generate_url');

        $offer->setOfferUrl($generateUrlService->generateOfferUrl($offer));

        $voteRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Vote')
        ;
        $vote = $voteRepository->findBy(array('voter' => $voter, 'offer' => $offer));

        if(isset($vote) && count($vote) > 0){
            $translated = $this->get('translator')->trans('offer.apply.already');
            $session->getFlashBag()->add('danger', $translated);
            return $this->redirectToRoute('dashboard_voter');
        }

        $em = $this->getDoctrine()->getManager();

        $vote = new Vote();
        $vote->setVoter($voter);
        $vote->setOffer($offer);
        $vote->setEstimation($estimation);
        if($interested == '1'){
            $vote->setInterested($interested);
        }

        $now =  new \DateTime();
        $vote->setDate($now);

        $em->persist($vote);
        $em->flush();

        $translated = $this->get('translator')->trans('offer.applied.success');
        $session->getFlashBag()->add('info', $translated);

        return $this->redirectToRoute('dashboard_voter');
    }

    public function offerNotFoundAction($id)
    {
        $offerRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Offer')
        ;
        $offer = $offerRepository->findOneBy(array('id' => $id));
        $similarOfferArray = $this->getSimilarOffers($offer);

        return $this->render('ProposerBundle:Offer:offerNotFound.html.twig', array(
            'similarOfferArray' => $similarOfferArray['offers'],
            'tags' => $similarOfferArray['tags'],
        ));
    }


    //@TODO put in CRON to turn every day
    public function sendEndActivationAction(Request $request){

        $now  =  new \DateTime("midnight");

        $last = $now->modify( '- 2 week' );

        $offerRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Offer')
        ;
        $offers = $offerRepository->findBy(array('validated' => 1, 'archived' => 0));

        $translated = $this->get('translator')->trans('email.endOfActivation.closed');

        $voteRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Vote')
        ;

        if(!empty($offers)){
            $mailer = $this->container->get('swiftmailer.mailer');
            foreach ($offers as $offer){
                if($offer->getActivationDate() == $last){
                    $averageValue = $voteRepository->getAverageValue($offer);
                    $message = (new \Swift_Message($translated))
                        ->setFrom('cproject@noreply.lu')
                        ->setTo($offer->getProposer()->getUser()->getEmail())
                        ->setBcc('commercial@cproject.lu')
                        ->setBody(
                            $this->renderView(
                                'AppBundle:Emails:endOfActivation.html.twig',
                                array('offer' => $offer,
                                    'averageValue' => $averageValue
                                )
                            ),
                            'text/html'
                        )
                    ;

                    $message->getHeaders()->addTextHeader(
                        CssInlinerPlugin::CSS_HEADER_KEY_AUTODETECT
                    );
                    $mailer->send($message);

                }



            }
        }

        return new Response();
    }

    public function incrementAction(Request $request)
    {
        $elementId = $request->get('id');
        $type = $request->get('type');

        $offerRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Offer')
        ;
        $offer = $offerRepository->findOneBy(array('id' => $elementId));

        if($type == 'countView'){
            $offer->setCountView($offer->getCountView() +1);
        }elseif ($type == 'countContact'){
            $offer->setCountContact($offer->getCountContact() +1);
        }

        $em = $this->getDoctrine()->getManager();

        $em->merge($offer);
        $em->flush();


        return new Response();
    }

    public function deleteImageAction(Request $request)
    {
        $imageId = $request->get('id');

        $imageRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Image')
        ;
        $image = $imageRepository->findOneBy(array('id' => $imageId));

        $offerRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Offer')
        ;
        $offer = $offerRepository->findOneBy(array('id' => $image->getOffer()->getId()));

        if(is_object($offer)){
            $offer->removeImage($image);
        }

        $em = $this->getDoctrine()->getManager();

        $em->merge($offer);
        $em->remove($image);
        $em->flush();


        return new Response();
    }

}