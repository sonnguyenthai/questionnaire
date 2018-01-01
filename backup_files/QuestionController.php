<?php
/**
 * Created by PhpStorm.
 * User: Asus-
 * Date: 27/12/2017
 * Time: 23:43
 */

namespace AppBundle\Controller;


use AppBundle\Entity\Question;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class QuestionController extends Controller
{
    public function listQuestionAction()
    {
        $em = $this->getDoctrine()->getManager();
        $question = $em->getRepository(Question::class)->findAll();

        return $this->render('listeQuestion.html.twig',['question'=>$question]);
    }

    public function viewQuestionAction($id)
    {
        $repository = $this->getDoctrine()->getManager()->getRepository(Question::class);
        $question = $repository->find($id);
        if (null === $question) {
            throw new NotFoundHttpException("La question d'id ".$id." n'existe pas.");
        }
        return $this->render('question.html.twig', array('question'=>$question));
    }

    public function addAction(Request $request)
    {

        // On crée un objet Question
        $question = new Question();
        //$question->setDate(new \Datetime());

        $form = $this->createFormBuilder($question)
            ->add('content',   TextType::class)
            ->add('question_type',     TextareaType::class)
            //->add('created_date', DateType::class, array('widget' => 'single_text', 'format' => 'yyyy-MM-dd',))
            ->add('save',      SubmitType::class)
            ->getForm();


        // Si la requête est en POST

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            // On vérifie que les valeurs entrées sont correctes
            // (Nous verrons la validation des objets en détail dans le prochain chapitre)
            if ($form->isValid()) {
                // On enregistre notre objet $advert dans la base de données, par exemple
                $em = $this->getDoctrine()->getManager();
                $em->persist($question);
                $em->flush();
                $request->getSession()->getFlashBag()->add('notice', 'Question bien enregistrée.');

                // On redirige vers la page de visualisation de la question  nouvellement créée
                return $this->redirectToRoute('question_view:', array('id' => $question->getId()));
            }
        }

        return $this->render('form.html.twig', array(
            'form' => $form->createView(),
        ));

    }

    public function updateAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $question = $em->getRepository(Question::class)->findById($id);
        $form = $this->createForm(Question::class,$id);

        return $this->handleForm($form,$question,$request);

    }

    public function deleteAction($id, Request $request)
    {

        $em = $this->getDoctrine()->getManager();
        $question = $em->getRepository('AppBundle:Question')->find($id);

        if (null === $question) {
            throw new NotFoundHttpException("La question d'id ".$id." n'existe pas.");
        }

        // On crée un formulaire vide, qui ne contiendra que le champ CSRF
        // Cela permet de protéger la suppression de la question contre cette faille
        $form = $this->get('form.factory')->create();

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em->remove($question);
            $em->flush();

            $request->getSession()->getFlashBag()->add('info', "La question a bien été supprimée.");

            return $this->redirectToRoute('questions_home');
        }

        return $this->render('delete.html.twig', array(
            'advert' => $question,
            'form'   => $form->createView(),
        ));
    }

    public function handleForm($form,  Question $question, Request $request)
    {
        $form->handleRequest($request);
            if($form->isSubmitted() && $form->isValid()){
                $question=$form->getData();
                $em=$this->getDoctrine()->getManager();
                $em->persist($question);
                $em->flush();

                return $this->redirectToRoute('listquestion',array('message'=>'succès'));
            }
        return $this->render('form.html.twig',array('form'=>$form->createView()));

    }
}