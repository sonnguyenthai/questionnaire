<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Answer;
use AppBundle\Entity\Survey;
use AppBundle\Entity\Question;
use AppBundle\Entity\Choice;
use AppBundle\Entity\SurveyQuestion;
use AppBundle\Entity\Respondent;
use AppBundle\Datatables\SurveyDatatable;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route; #DOT NOT UNCOMMENT EVEN IF AN ERROR OCCUR

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SurveyController extends Controller
{

    /**
     * Lists all Survey entities.
     *
     * @Route("/surveys", name="list_surveys")
     */
    public function listSurveysAction(Request $request){
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->getUser();

        $isAjax = $request->isXmlHttpRequest();
        $datatable = $this->get('sg_datatables.factory')->create(SurveyDatatable::class);
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

        return $this->render('survey/listSurveys.html.twig', array(
            'datatable' => $datatable,
        ));

    }

    /**
     * Add a new Survey
     *
     * @Route("/survey/add", name="survey_add")
     */
    public function addSurveyAction(Request $request){
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->getUser();

        $survey = new Survey();
        $survey->setUser($user);

        $repository = $this->getDoctrine()->getRepository(Question::class);
        $availableQuestions = $repository->findByUser($user);

        $form = $this->createFormBuilder()
            ->add('name', TextType::class)
            ->add('description', TextareaType::class)
            ->add('questions', ChoiceType::class, [
                'choices' => $availableQuestions,
                'multiple'=> true,
                'choice_label' => function($question, $key, $index) {
                    /** @var Category $category */
                    return $question->getContent();
                }
                ])
            ->add('save', SubmitType::class, array('label' => 'Finish'))
            ->add('next', SubmitType::class, array('label' => 'Save And Add New Question'))
            ->getForm();

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                // $form->getData() holds the submitted values
                // but, the original `$task` variable has also been updated
                $questions = $form->get('questions')->getData();
                $survey->setDescription($form->get('description')->getData());
                $survey->setName($form->get('name')->getData());
                $em = $this->getDoctrine()->getManager();
                $em->persist($survey);
                $em->flush();
                if ($questions)
                    foreach($questions as $qu){
                        $q = $repository->find($qu);
                        if ($q){
                            $survey_question = new SurveyQuestion();
                            $survey_question->setQuestion($q);
                            $survey_question->setSurvey($survey);
                            $em->persist($survey_question);
                            $em->flush();
                        }
                    }
                if ($form->get('next')->isClicked()){
                    return $this->redirectToRoute('question_add', array('survey' => $survey->getId()));
                }
                return $this->redirectToRoute('list_surveys');
            }

        }
        return $this->render('survey/addSurvey.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * Edit a survey
     *
     * @Route("/survey/{id}/edit", name="survey_edit")
     */
    public function editSurveyAction(){

    }

    /**
     * Remove a survey
     *
     * @Route("/survey/{id}/delete", name="survey_delete")
     */
    public function deleteSurveyAction($id, Request $request){
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->getUser();

        $em = $this->getDoctrine()->getManager();
        $survey = $em->getRepository('AppBundle:Survey')->find($id);

        if (null === $survey) {
            throw new NotFoundHttpException("The survey with id ".$id." doesn't exist");
        }

        if ($survey->getUser() != $user){
            $this->addFlash('danger', "The survey with id ".$id." does't belong to you");
            return $this->redirectToRoute('list_surveys');
        }

        $form = $this->createFormBuilder()->getForm();

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em->remove($survey);
            $em->flush();

            $this->addFlash('success', "The survey with id ".$id." is deleted successfully");

            return $this->redirectToRoute('list_surveys');
        }

        return $this->render('survey/deleteSurvey.html.twig', array(
            'survey' => $survey,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Show a survey
     *
     * @Route("/survey/{id}/show", name="survey_show")
     */
    public function showSurveyAction(){

    }

    /**
     * Public view of a survey
     *
     * @Route("/survey/{id}/view", name="survey_view_public")
     */
    public function publicViewSurveyAction($id, Request $request){
        $em = $this->getDoctrine()->getManager();
        $survey = $em->getRepository('AppBundle:Survey')->find($id);

        if (null === $survey) {
            throw new NotFoundHttpException("The survey with id ".$id." doesn't exist");
        }
        $respondent = new Respondent();

        $respondent->setSurvey($survey);


        $form = $this->createFormBuilder()->getForm();

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $respondent->setName($request->request->get('respondent_name', ""));
            $em->persist($respondent);
            $em->flush();

            $sQuestions = $survey->getQuestions();
            foreach ($sQuestions as $sQuestion){
                $answer = new Answer();
                $answer->setRespondent($respondent);
                $answer->setSurveyQuestion($sQuestion);
                $answer_name = "answer_".$sQuestion->getId();
                $question = $sQuestion->getQuestion();
                if ($question->getQuestionType() == "text"){
                    $answer->setContent($request->request->get($answer_name, ""));
                }elseif ($question->getQuestionType() == "single"){
                    $choice_id = $request->request->get($answer_name);
                    $choice = $em->getRepository("AppBundle:Choice")->find($choice_id);
                    if ($choice){
                        $answer->setContent("");
                        $answer->addChoice($choice);
                    }
                }else{
                    $choices = $request->request->get($answer_name);
                    $answer->setContent("");
                    foreach ($choices as $choiceId){
                        $choice = $em->getRepository("AppBundle:Choice")->find($choiceId);
                        if ($choice){
                            $answer->addChoice($choice);
                        }
                    }
                }
                $em->persist($answer);
                $em->flush();
            }

            $this->addFlash('success', "Survey completed!");

            return $this->redirectToRoute('homepage');
        }

        return $this->render('survey/publicView.html.twig', array('survey'=>$survey, 'form'=>$form->createView()));

    }
}
