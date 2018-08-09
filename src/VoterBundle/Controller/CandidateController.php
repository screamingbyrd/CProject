<?php
/**
 * Created by PhpStorm.
 * User: Altea IT
 * Date: 28/05/2018
 * Time: 12:00
 */

namespace VoterBundle\Controller;

use AppBundle\Entity\Voter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Form\VoterType;
use Symfony\Component\HttpFoundation\Response;
use Trt\SwiftCssInlinerBundle\Plugin\CssInlinerPlugin;

class VoterController extends Controller
{
    public function createAction(Request $request)
    {
        $idOffer = $request->get('offerId');
        if(isset($idOffer)){
            $_SESSION['offerId'] = $idOffer;
        }

        $session = $request->getSession();

        $voter = new Voter();

        $form = $this->get('form.factory')->create(VoterType::class);

        // Si la requête est en POST
        if ($request->isMethod('POST')) {


            // On fait le lien Requête <-> Formulaire
            // À partir de maintenant, la variable $advert contient les valeurs entrées dans le formulaire par le visiteur
            $form->handleRequest($request);
            // On vérifie que les valeurs entrées sont correctes
            // (Nous verrons la validation des objets en détail dans le prochain chapitre)
            if ($form->isValid()) {

                $data = $form->getData();

                $userRegister = $this->get('app.user_register');


                $user = $userRegister->register($data->getEmail(),$data->getEmail(),$data->getPassword(),$data->getFirstName(),$data->getLastName(), 'ROLE_CANDIDATE');


                if($user != false){
                $voter->setUser($user);
                $voter->setDescription($data->getDescription());
                $voter->setAge($data->getAge());
                $voter->setExperience($data->getExperience());
                $voter->setLicense($data->getLicense());
                $voter->setDiploma($data->getDiploma());
                $voter->setSocialMedia($data->getSocialMedia());
                $voter->setPhone($data->getPhone());

                // On enregistre notre objet $advert dans la base de données, par exemple
                $em = $this->getDoctrine()->getManager();
                $em->persist($voter);
                $em->flush();

                $translated = $this->get('translator')->trans('form.registration.successVoter');
                $session->getFlashBag()->add('info', $translated);

                if(isset($_SESSION['offerId'])){
                    $id = $_SESSION['offerId'];
                    unset($_SESSION['offerId']);
                    $offerRepository = $this
                        ->getDoctrine()
                        ->getManager()
                        ->getRepository('AppBundle:Offer')
                    ;
                    $offer = $offerRepository->find($id);
                    $generateUrlService = $this->get('app.offer_generate_url');
                    $offer->setOfferUrl($generateUrlService->generateOfferUrl($offer));

                    return $this->redirectToRoute('show_offer', array('id' => $id, 'url' => $offer->getOfferUrl()));
                }else{
                    return $this->redirectToRoute('edit_voter');
                }



                }else{
                    $translated = $this->get('translator')->trans('form.registration.error');
                    $session->getFlashBag()->add('danger', $translated);

                    return $this->redirectToRoute('jobnow_home');
                }
            }
        }



        // On passe la méthode createView() du formulaire à la vue
        // afin qu'elle puisse afficher le formulaire toute seule
        return $this->render('VoterBundle:Voter:create.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function editAction(Request $request ){
        $user = $this->getUser();
        $session = $request->getSession();
        $idVoter = $request->get('id');

        $voterRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Voter')
        ;
        $voter = $voterRepository->findOneBy(isset($idVoter)?array('id' => $idVoter):array('user' => $user->getId()));

        if(!((isset($user) and in_array('ROLE_CANDIDATE', $user->getRoles())) ||  in_array('ROLE_ADMIN', $user->getRoles()))){
            $translated = $this->get('translator')->trans('redirect.voter');
            $session->getFlashBag()->add('danger', $translated);
            return $this->redirectToRoute('create_voter');
        }

        $originalCvPath = $voter->getCv();
        $titleCV = null;
        if (isset($originalCvPath)){
            $titleCV = substr($originalCvPath, strrpos($originalCvPath, '%') + 1);
        }

        $originalCoverLetterPath = $voter->getCoverLetter();
        $titleCoverLetter = null;
        if(isset($originalCoverLetterPath)){
            $titleCoverLetter = substr($originalCoverLetterPath, strrpos($originalCoverLetterPath, '%') + 1);
        }


        $userRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:User')
        ;
        $user = $userRepository->findOneBy(array('id' => $voter->getUser()));



        $voter->setFirstName($voter->getUser()->getFirstName());
        $voter->setLastName($voter->getUser()->getLastName());
        $voter->setEmail($voter->getUser()->getEmail());

        $form = $this->get('form.factory')->create(VoterType::class, $voter);

        $form->remove('password');
        $form->remove('terms');

        // Si la requête est en POST
        if ($request->isMethod('POST')) {

            $form->handleRequest($request);
            if ($form->isValid()) {

                $data = $form->getData();

                $userManager = $this->get('fos_user.user_manager');

                $user->setEmail($data->getEmail());
                $user->setEmailCanonical($data->getEmail());
                $user->setFirstName($data->getFirstName());
                $user->SetLastName($data->getLastName());
                $userManager->updateUser($user);

                $voter->setDescription($data->getDescription());
                $voter->setAge($data->getAge());
                $voter->setExperience($data->getExperience());
                $voter->setLicense($data->getLicense());
                $voter->setDiploma($data->getDiploma());
                $voter->setSocialMedia($data->getSocialMedia());
                $voter->setPhone($data->getPhone());
                $voter->setModifiedDate( new \datetime());

                $target_dir = "uploads/images/voter/";
                $newCv = $data->getCv();
                if(isset($newCv) && is_object($newCv)){

                    $target_file = $target_dir . md5(uniqid()) . '%' . basename($newCv->getClientOriginalName());
                    move_uploaded_file($newCv->getPathname(), $target_file);

                    if(isset($originalCvPath) && $originalCvPath != ''){
                        unlink($originalCvPath);
                    }
                    $voter->setCv($target_file);
                }

                $newCoverLetter = $data->getCoverLetter();
                if(isset($newCoverLetter) && is_object($newCoverLetter)){
                    $target_file_cover = $target_dir . md5(uniqid()) . '%' . basename($newCoverLetter->getClientOriginalName());
                    move_uploaded_file($newCoverLetter->getPathname(), $target_file_cover);

                    if(isset($originalCoverLetterPath) && $originalCoverLetterPath != ''){
                        unlink($originalCoverLetterPath);
                    }
                    $voter->setCoverLetter($target_file_cover);
                }

                $em = $this->getDoctrine()->getManager();
                $em->merge($voter);
                $em->flush();

                $translated = $this->get('translator')->trans('form.registration.editedVoter');
                $session->getFlashBag()->add('info', $translated);

                return $this->redirectToRoute('show_voter', array('id' => $voter->getId()) );
            }
        }

        $completion = 3;

        $title = $voter->getTitle();
        if(isset($title)){
            $completion += 1;
        }
        $description = $voter->getDescription();
        if(isset($description)){
            $completion += 1;
        }
        $age = $voter->getAge();
        if(isset($age)){
            $completion += 1;
        }
        $experience = $voter->getExperience();
        if(isset($experience)){
            $completion += 1;
        }
        $diploma = $voter->getDiploma();
        if(isset($diploma)){
            $completion += 1;
        }
        $socialMedia = $voter->getSocialMedia();
        if(isset($socialMedia)){
            $completion += 1;
        }
        $phone = $voter->getPhone();
        if(isset($phone)){
            $completion += 1;
        }
        if(isset($voter->getLicense()[0])){
            $completion += 1;
        }
        if(isset($voter->getLanguage()[0])){
            $completion += 1;
        }
        if(isset($voter->getTag()[0])){
            $completion += 1;
        }
        if(isset($voter->getSearchedTag()[0])){
            $completion += 1;
        }

        $completion = $completion/14 * 100;

        return $this->render('VoterBundle:Voter:edit.html.twig', array(
            'form' => $form->createView(),
            'completion' => $completion,
            'user' => $user,
            'cvTitle' => $titleCV,
            'coverLetterTitle' => $titleCoverLetter
        ));
    }

    public function dashboardAction(Request $request){
        $user = $this->getUser();

        $idVoter = $request->get('id');
        $session = $request->getSession();

        $generateUrlService = $this->get('app.offer_generate_url');

        $voterRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Voter')
        ;
        $voter = $voterRepository->findOneBy(isset($idVoter)?array('id' => $idVoter):array('user' => $user->getId()));

        if(!((isset($user) and in_array('ROLE_CANDIDATE', $user->getRoles())) ||  in_array('ROLE_ADMIN', $user->getRoles()))){
            $translated = $this->get('translator')->trans('redirect.voter');
            $session->getFlashBag()->add('danger', $translated);
            return $this->redirectToRoute('create_voter');
        }

        $proposerRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Proposer')
        ;
        $favoriteRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Favorite')
        ;
        $tagRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Tag')
        ;

        $notificationRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Notification')
        ;
        $notifications = $notificationRepository->findBy(array('voter' => $voter));
        $favorites = $favoriteRepository->findBy(array('voter' => $voter));

        foreach ($favorites as &$favorite){
            $favorite->getOffer()->setOfferUrl($generateUrlService->generateOfferUrl($favorite->getOffer()));
        }

        $notificationsArray = array();

        foreach ($notifications as $notification){
            $newNotification = array();
            $newNotification['uid'] = $notification->getUid();
            $newNotification['elementId'] = $notification->getElementId();
            $type = $notification->getTypeNotification();
            $newNotification['type'] = $type;
            if($type == 'notification.proposer'){
                $proposer = $proposerRepository->findOneBy(array('id' => $notification->getElementId()));
                $newNotification['name'] = $proposer->getName();
            }elseif ($type == 'notification.tag'){
                $tag = $tagRepository->findOneBy(array('id' => $notification->getElementId()));
                $newNotification['name'] = $tag->getName();
            }
            $notificationsArray[] = $newNotification;
        }

        $voteRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Vote')
        ;
        $votes = $voteRepository->findBy(array('voter' => $voter));

        $offerIdArray = $finalArray = array();

        foreach ($votes as $vote) {
            $offerIdArray[] = $vote->getOffer()->getId();
            $finalArray[$vote->getOffer()->getId()]['date'] = $vote->getDate();
        }

        $offerRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Offer')
        ;
        $offers = $offerRepository->findById($offerIdArray);

        $tagRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Tag')
        ;
        $tags = $tagRepository->findAll();

        foreach ($offers as &$offer){
            $offer->setOfferUrl($generateUrlService->generateOfferUrl($offer));
            $finalArray[$offer->getId()]['offer'] = $offer;
        }

        return $this->render('VoterBundle:Voter:dashboard.html.twig',
            array(
                'appliedOffer' => $finalArray,
                'notifications' => $notificationsArray,
                'favorites' => $favorites,
                'tags' => $tags
            ));
    }

    public function deleteAction(Request $request){

        $session = $request->getSession();
        $user = $this->getUser();
        $idVoter = $request->get('id');
        $voterRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Voter')
        ;
        $voter = $voterRepository->findOneBy(isset($idVoter)?array('id' => $idVoter):array('user' => $user->getId()));

        if(!((isset($user) and in_array('ROLE_CANDIDATE', $user->getRoles())) ||  in_array('ROLE_ADMIN', $user->getRoles()))){
            $translated = $this->get('translator')->trans('redirect.voter');
            $session->getFlashBag()->add('danger', $translated);
            return $this->redirectToRoute('create_voter');
        }
        $em = $this->getDoctrine()->getManager();

        if(in_array('ROLE_ADMIN', $user->getRoles())){
            $user = $voter->getUser();
        }

        $postulatedRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Vote')
        ;
        $postulated = $postulatedRepository->findBy(array('voter' => $voter));
        foreach ($postulated as $offer){
            $em->remove($offer);
        }
        
        $favoriteRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Favorite')
        ;
        $favorites = $favoriteRepository->findBy(array('voter' => $voter));
        foreach ($favorites as $favorite){
            $em->remove($favorite);
        }

        $mail = $user->getEmail();

        $cv = $voter->getCv();

        if(isset($cv)){
            unlink($cv);
        }

        $em->remove($voter);
        $em->remove($user);
        $em->flush();

        $mailer = $this->container->get('swiftmailer.mailer');

        $message = (new \Swift_Message($translated = $this->get('translator')->trans('email.deleted')))
            ->setFrom('jobnowlu@noreply.lu')
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

        $translated = $this->get('translator')->trans('voter.delete.deleted');
        $session->getFlashBag()->add('info', $translated);

        return $this->redirectToRoute('jobnow_home');
    }

    public function showAction(Request $request, $id){
        $user = $this->getUser();

        $session = $request->getSession();

        if(!(isset($user) and in_array('ROLE_EMPLOYER', $user->getRoles()) || in_array('ROLE_ADMIN', $user->getRoles()) || $user->getId() != $id)){
            $translated = $this->get('translator')->trans('redirect.proposer');
            $session->getFlashBag()->add('danger', $translated);
            return $this->redirectToRoute('create_proposer');
        }

        $voterRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Voter')
        ;
        $voter = $voterRepository->findOneBy(array('id' => $id));

        return $this->render('VoterBundle:Voter:show.html.twig', array(
            'voter' => $voter,
        ));
    }

    public function searchAction(){
        $voterRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Voter')
        ;
        $voters = $voterRepository->findAll();

        return $this->render('VoterBundle:Voter:search.html.twig', array(
            'voters' => $voters,
        ));
    }

    //@TODO put in CRON
    public function eraseUnusedCvsAction(){
        $voterRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Voter')
        ;
        $voteRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Vote')
        ;
        $voters = $voterRepository->findAll();
        $em = $this->getDoctrine()->getManager();
        foreach ($voters as $voter){
            $recentOffers = $voteRepository->getRecentVote($voter);
            if(empty($recentOffers)){
                $cvLink = $voter->getCv();
                $coverLink = $voter->getCoverLetter();
                if(isset($cvLink) and $cvLink != ''){
                    if(file_exists($cvLink)){
                        unlink($cvLink);
                    }
                    $voter->setCv(null);
                }
                if(isset($coverLink) and $coverLink != ''){
                    if(file_exists($coverLink)){
                        unlink($coverLink);
                    }
                    $voter->setCoverLetter(null);
                }
                $em->merge($voter);
            }
        }
        $em->flush();
        return new Response();
    }

}