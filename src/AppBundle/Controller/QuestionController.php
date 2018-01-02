<?php
/**
 * Created by PhpStorm.
 * User: Asus-
 * Date: 27/12/2017
 * Time: 23:43
 */

namespace AppBundle\Controller;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route; #DOT NOT UNCOMMENT EVEN IF AN ERROR OCCUR
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use AppBundle\Entity\Question;
use AppBundle\Entity\Choice;
use AppBundle\Entity\Survey;
use AppBundle\Entity\SurveyQuestion;
use AppBundle\Datatables\QuestionDatatable;
use AppBundle\Form\QuestionType;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;

class QuestionController extends Controller
{
    /**
     * Lists all Question entities.
     *
     * @Route("/questions", name="list_questions")
     */
    public function listQuestionAction(Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->getUser();

        $isAjax = $request->isXmlHttpRequest();
        $datatable = $this->get('sg_datatables.factory')->create(QuestionDatatable::class);
        $datatable->buildDatatable();

        if ($isAjax) {
            $responseService = $this->get('sg_datatables.response');
            $responseService->setDatatable($datatable);
            $datatableQueryBuilder = $responseService->getDatatableQueryBuilder();

            $qb = $datatableQueryBuilder->getQb();
            $qb->andWhere('user.username = :username');
            $qb->setParameter('username', $user->getUsername());

            return $responseService->getResponse();
        }

        return $this->render('question/listQuestions.html.twig', array(
            'datatable' => $datatable,
        ));
    }

    public function viewAction($id)
    {
        $repository = $this->getDoctrine()->getManager()->getRepository(Question::class);
        $question = $repository->find($id);
        if (null === $question) {
            throw new NotFoundHttpException("La question d'id ".$id." n'existe pas.");
        }
        return $this->render('question.html.twig', array('question'=>$question));

    }

    /**
     * Add new question.
     *
     * @Route("/question/add", name="question_add")
     */
    public function addAction(Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->getUser();

        // On crée un objet Question
        $question = new Question();
        $question->setUser($user);

        $form = $this->createForm(QuestionType::class, $question);


        // Si la requête est en POST

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            // On vérifie que les valeurs entrées sont correctes
            // (Nous verrons la validation des objets en détail dans le prochain chapitre)
            if ($form->isValid()) {

                // DatatablesBundle has an error in escaping out put. So we need to filter input. Did report this error and ask them to fix
                $question->setContent(strip_tags($form->get('content')->getData()));

                $em = $this->getDoctrine()->getManager();
                $em->persist($question);
                $em->flush();

                $choices = $request->request->get('choice');
                if ($choices)
                    foreach($choices as $choice){
                        $ch = new Choice();
                        $ch->setContent($choice);
                        $ch->setQuestion($question);
                        $em->persist($ch);
                        $em->flush();
                    }

                $survey_id = $request->query->get('survey');
                if ($survey_id){
                    $survey_repo = $this->getDoctrine()->getRepository(Survey::class);
                    $survey = $survey_repo->find($survey_id);
                    if ($survey){
                        if ($survey->getUser() == $user){
                            $survey_question = new SurveyQuestion();
                            $survey_question->setQuestion($question);
                            $survey_question->setSurvey($survey);
                            $em->persist($survey_question);
                            $em->flush();
                        }
                    }
                }

                $this->addFlash('success', 'Created successfully Question object with ID = '.$question->getId());

                // On redirige vers la page de visualisation de la question  nouvellement créée
                return $this->redirectToRoute('list_questions');
            }
        }

        return $this->render('question/addQuestion.html.twig', array(
            'form' => $form->createView(),
        ));

    }

    /**
     * Delete a Question.
     *
     * @Route("/question/{id}/delete", name="question_delete", requirements={"id" = "\d+"})
     */
    public function deleteAction($id, Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->getUser();

        $em = $this->getDoctrine()->getManager();
        $question = $em->getRepository('AppBundle:Question')->find($id);

        if (null === $question) {
            throw new NotFoundHttpException("La question d'id ".$id." n'existe pas.");
        }

        if ($question->getUser() != $user){
            $this->addFlash('danger', 'The question with ID '.$id.' does not belong to you');
            return $this->redirectToRoute('list_questions');
        }

        // On crée un formulaire vide, qui ne contiendra que le champ CSRF
        // Cela permet de protéger la suppression de la question contre cette faille
        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('question_delete', array('id'=>$id)))->getForm();

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em->remove($question);
            $em->flush();

            $this->addFlash('success', "La question ".$id." a bien été supprimée.");

            return $this->redirectToRoute('list_questions');
        }

        return $this->render('question/deleteQuestion.html.twig', array(
            'question' => $question,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Update question with id number {id}.
     *
     * @Route("/question/{id}/edit", name="question_edit", requirements={"id" = "\d+"})
     * @Method({"POST", "GET"})
     */
    public function updateAction($id, Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->getUser();

        $em = $this->getDoctrine()->getManager();
        $question = $em->getRepository('AppBundle:Question')->find($id);
        if (!$question) {
            throw $this->createNotFoundException(
                'No product found for id '.$id
            );
        }

        if ($question->getUser() != $user){
            $this->addFlash('danger', 'The question with ID '.$id.' does not belong to you');
            return $this->redirectToRoute('list_questions');
        }

        $form = $this->createForm(QuestionType::class, $question);
        $choices = $question->getChoices();

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $question = $form->getData();
            // DatatablesBundle has an error in escaping out put. So we need to filter input. Did report this error and ask them to fix
            $question->setContent(strip_tags($form->get('content')->getData()));

            if ($form->get('question_type')->getData() == "text"){
                foreach ($choices as $choice){
                    $question->removeChoice($choice);
                }
            }
            $question->setModifiedDate(new \DateTime('now'));

            $em=$this->getDoctrine()->getManager();
            $em->persist($question);
            $em->flush();

            $this->addFlash('success', 'Your changes on Question object '.$id.' are saved!');
            return $this->redirectToRoute('list_questions');
        }
        return $this->render('question/editQuestion.html.twig',
            array('form'=>$form->createView(),
                'choices'=>$choices,
                'question'=>$question
                ));
    }

    /**
     * Add Choice entity
     *
     * @Route("question/{id}/add-choice", name="choice_add", requirements={"id" = "\d+"})
     * @Method({"POST", "GET"})
     */
    public function addChoiceAction($id, Request $request){
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $em = $this->getDoctrine()->getManager();
        $question = $em->getRepository('AppBundle:Question')->find($id);
        if ($question) {
            $choice = new Choice();
            $choice->setQuestion($question);
            $form = $this->createFormBuilder($choice)
                ->setAction($this->generateUrl('choice_add', array('id'=>$id)))
                ->add('content', TextType::class, array('required'=>1))->getForm();

            if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
                $em->persist($choice);
                $em->flush();
                return $this->redirectToRoute('question_edit',array('id'=>$id));
            }
            return $this->render("question/addChoiceModal.html.twig",
                array('form'=>$form->createView(),
                    ));
        }else{
            $this->addFlash('danger', 'Can not find Question object with ID ='.$id);
            return $this->redirectToRoute("list_questions");
        }
    }

    /**
     * Remove a Choice entity
     *
     * @Route("question/{question_id}/choice/{id}/delete", name="choice_delete", requirements={"id" = "\d+", "question_id" = "\d+"})
     * @Method({"POST", "GET"})
     */
    public function removeChoiceAction($question_id, $id, Request $request){
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $em = $this->getDoctrine()->getManager();
        $choice = $em->getRepository('AppBundle:Choice')->find($id);

        if ($choice) {
            if ($choice->getQuestion()->getId() != $question_id){
                $this->addFlash('danger', 'The specific choice doesnt belong to this question');
                return $this->redirectToRoute('question_edit',array('id'=>$question_id));
            }

            $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('choice_delete', array('id'=>$id, 'question_id'=>$question_id)))->getForm();

            if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
                $em->remove($choice);
                $em->flush();

                $this->addFlash('success', 'Removed one choice successfully');
                return $this->redirectToRoute('question_edit',array('id'=>$question_id));
            }
            return $this->render("question/deleteChoice.html.twig",
                array('form'=>$form->createView(),
                ));
        }else{
            $this->addFlash('danger', 'Choice not found for ID '.$id);
            return $this->redirectToRoute('question_edit',array('id'=>$question_id));
        }
    }

}