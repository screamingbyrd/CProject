<?php
/**
 * Created by PhpStorm.
 * User: Altea IT
 * Date: 30/05/2018
 * Time: 16:21
 */

namespace AppBundle\Controller;

use AppBundle\Entity\LogCredit;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Response;
use Spipu\Html2Pdf\Html2Pdf;


class CreditController extends Controller
{

    public function creditAction(Request $request, $id = 0)
    {

        $creditInfo = $this->container->get('app.credit_info');

        switch ($id) {

            case 1: {

                return $this->buyPack($request,$creditInfo->getOneCredit(),1);
                break;
            }
            case 2: {
                return $this->buyPack($request,$creditInfo->getFiveCredit(),5);
                break;

            }
            case 3: {
                return $this->buyPack($request,$creditInfo->getTenCredit(),10);
                break;

            }
            default: {
                $creditProposer = 0;
                $logsCredit = 0;
                $user =$this->getUser();
                if(isset($user)){
                    $proposer = $this->getUser()->getProposer();

                    if(isset($proposer)){
                        $creditProposer = $proposer->getCredit();

                        $repository = $this
                            ->getDoctrine()
                            ->getManager()
                            ->getRepository('AppBundle:LogCredit')
                        ;
                        $logsCredit = $repository->findBy(array('proposer' => $proposer));
                    }

                }

                return $this->render('AppBundle:Credit:credit.html.twig', array(
                    'credit' => $creditProposer,
                    'logsCredit' => $logsCredit,
                    'creditService' => $creditInfo
                ));
            }
        }
    }

    public function billsAction(Request $request){
        $currentPage = $request->get('row');
        $sort = $request->get('sort');
        $currentPage = isset($currentPage)?$currentPage:1;
        $sort = isset($sort)?$sort:'DESC';

        $numberOfItem = 50;

        $user = $this->getUser();

        $session = $request->getSession();

        if(!((isset($user) and in_array('ROLE_PROPOSER', $user->getRoles())) ||  (isset($user) and in_array('ROLE_ADMIN', $user->getRoles())))){
            $translated = $this->get('translator')->trans('redirect.proposer');
            $session->getFlashBag()->add('danger', $translated);
            return $this->redirectToRoute('create_proposer');
        }

        $proposer = $this->getUser()->getProposer();

        $idProposer = $request->get('id');

        if(isset($idProposer) && in_array('ROLE_ADMIN', $user->getRoles())){
            $repository = $this
                ->getDoctrine()
                ->getManager()
                ->getRepository('AppBundle:Proposer')
            ;
            $proposer = $repository->findOneBy(array('id' => $idProposer));
        }

        $repository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:LogCredit')
        ;
        $logsCredit = $repository->findBy(array('proposer' => $proposer),array('date' => $sort));

        $countResult = count($logsCredit);

        $finalArray = array_slice($logsCredit, ($currentPage - 1 ) * $numberOfItem, $numberOfItem);

        $totalPage = ceil ($countResult / $numberOfItem);

        return $this->render('AppBundle:Credit:bills.html.twig', [
            'logsCredit' => $finalArray,
            'page' => $currentPage,
            'total' => $totalPage,
            'sort' => $sort,
            'id' => $idProposer
        ]);

    }

    private function buyPack(Request $request, $price, $nbrCredit ){

        $user = $this->getUser();

        $session = $request->getSession();

        if(!(isset($user) and  in_array('ROLE_PROPOSER', $user->getRoles()))){
            $translated = $this->get('translator')->trans('redirect.proposer');
            $session->getFlashBag()->add('danger', $translated);
            return $this->redirectToRoute('create_proposer');
        }

        $form = $this->get('form.factory')
            ->createNamedBuilder('payment-form')
            ->add('token', HiddenType::class, [
                'constraints' => [new NotBlank()],
            ])
            ->add('name',      TextType::class, array(
                'required' => true,
                'label' => 'dashboard.voter.name'
            ))
            ->add('phone',      TextType::class, array(
                'required' => true,
                'label' => 'form.registration.phone'
            ))
            ->add('location',      TextType::class, array(
                'required' => true,
                'label' => 'offer.location',
            ))
            ->add('zipcode',      TextType::class, array(
                'required' => true,
                'label' => 'price.payment.zipcode'
            ))
            ->add('submit', SubmitType::class)
            ->getForm();
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                try {

                    $data = $form->getData();

                    $this->get('app.client.stripe')->createPremiumCharge($this->getUser(), $data['token'] ,$price,$this->getUser()->getProposer(),$nbrCredit);

                    //Logging credit purchase in db
                    $logCredit = new LogCredit();
                    $em = $this->getDoctrine()->getManager();

                    $logCredit->setDate(new \DateTime());
                    $logCredit->setCredit($nbrCredit);
                    $logCredit->setProposer($this->getUser()->getProposer());
                    $logCredit->setPrice($price);
                    $logCredit->setName($data['name']);
                    $logCredit->setPhone($data['phone']);
                    $logCredit->setLocation($data['location']);
                    $logCredit->setZipcode($data['zipcode']);

                    $em->persist($logCredit);
                    $em->flush();

                    $translated = $this->get('translator')->trans('price.payment.success', array('%credits%' => $nbrCredit));
                    $session->getFlashBag()->add('info', $translated);

                    return $this->redirectToRoute('jobnow_credit');

                } catch (\Stripe\Error\Base $e) {
                    $this->addFlash('warning', sprintf($this->get('translator')->trans('price.payment.unable'), $e instanceof \Stripe\Error\Card ? lcfirst($e->getMessage()) : $this->get('translator')->trans('price.payment.please')));
                    return $this->redirectToRoute('jobnow_payment', array('id' => 1));
                }

            }
        }
        return $this->render('AppBundle:Credit:stripePayment.html.twig', [
            'form' => $form->createView(),
            'stripe_public_key' => $this->getParameter('stripe_public_key'),
            'price' => $price,
            'nbrCredit' => $nbrCredit
        ]);

    }

    public function generateBillAction(Request $request)
    {
        $id = $request->get('id');
        $session = $request->getSession();
        $user = $this->getUser();

        $repository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:LogCredit')
        ;
        $logsCredit = $repository->findOneBy(array('id' => $id));

        if(!(isset($user) and  (in_array('ROLE_PROPOSER', $user->getRoles()) and $logsCredit->getProposer() == $user->getProposer()) or in_array('ROLE_ADMIN', $user->getRoles()))){
            $translated = $this->get('translator')->trans('redirect.proposer');
            $session->getFlashBag()->add('danger', $translated);
            return $this->redirectToRoute('create_proposer');
        }

        $html2pdf = new Html2Pdf();

        $html = $this->renderView('AppBundle:Credit:billsPdf.html.twig', array(
            'logCredit' => $logsCredit
        ));

//        return $this->render('AppBundle:Credit:billsPdf.html.twig', array(
//            'logCredit' => $logsCredit
//        ));

        $html2pdf->writeHTML($html);

        return new Response($html2pdf->output(), 200, array(
            'Content-Type' => 'application/pdf'));
    }

}