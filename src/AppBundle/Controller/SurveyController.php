<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Answer;
use AppBundle\Entity\Survey;
use AppBundle\Entity\Question;
use AppBundle\Entity\SurveyQuestion;
use AppBundle\Entity\Respondent;
use AppBundle\Datatables\SurveyDatatable;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route; #DOT NOT UNCOMMENT EVEN IF AN ERROR OCCUR

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


class SurveyController extends Controller
{


    /**
     * Lists all Survey entities.
     *
     * @Route("/surveys", name="list_surveys")
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
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
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
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
                    return $question->getContent();
                }
                ])
            ->add('save', SubmitType::class, array('label' => 'Finish'))
            ->add('next', SubmitType::class, array('label' => 'Save And Add New Question'))
            ->getForm();

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $questions = $form->get('questions')->getData();

                // DatatablesBundle has an error in escaping out put. So we need to filter input. Did report this error and ask them to fix
                $survey->setDescription(strip_tags($form->get('description')->getData()));
                $survey->setName(strip_tags($form->get('name')->getData()));
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
                $this->addFlash("success", "Added survey ".$survey->getId()." successfully");
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
     * @Route("/survey/{id}/edit", name="survey_edit", requirements={"id" = "\d+"})
     */
    public function editSurveyAction($id, Request $request){
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

        $repository = $this->getDoctrine()->getRepository(Question::class);

        $form = $this->createFormBuilder($survey)
            ->add('name', TextType::class)
            ->add('description', TextareaType::class)
            ->add('save', SubmitType::class, array('label' => 'Finish'))
            ->getForm();

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {

                // DatatablesBundle has an error in escaping out put. So we need to filter input. Did report this error and ask them to fix
                $survey->setDescription(strip_tags($form->get('description')->getData()));
                $survey->setName(strip_tags($form->get('name')->getData()));
                $em = $this->getDoctrine()->getManager();
                $em->persist($survey);
                $em->flush();

                $this->addFlash("success", "Updated survey ".$survey->getId()." successfully");
                return $this->redirectToRoute('list_surveys');
            }

        }
        return $this->render('survey/editSurvey.html.twig', array(
            'form' => $form->createView(),
            'survey' => $survey
        ));
    }


    /**
     * Remove a question from a survey
     *
     * @Route("/survey/{id}/remove-question/{question_id}", name="survey_question_remove", requirements={"id" = "\d+", "question_id" = "\d+"})
     *
     * @param $id, $question_id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function deleteSurveyQuestionAction($id, $question_id, Request $request){
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

        $survey_question = $em->getRepository('AppBundle:SurveyQuestion')->find($question_id);

        if ($survey_question->getSurvey() != $survey){
            $this->addFlash('danger', "The specific question does not belong to this survey");
            return $this->redirectToRoute('survey_edit', array('id'=>$id));
        }

        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('survey_question_remove', array('id'=>$id, 'question_id'=>$question_id)))
            ->getForm();

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em->remove($survey_question);
            $em->flush();

            $this->addFlash('success', "The question with id ".$question_id." is removed successfully");

            return $this->redirectToRoute('survey_edit', array('id'=>$id));
        }

        return $this->render('survey/removeSurveyQuestion.html.twig', array(
            'survey' => $survey,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Delete a survey
     *
     * @Route("/survey/{id}/delete", name="survey_delete", requirements={"id" = "\d+"})
     *
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
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

        $form = $this->createFormBuilder()
            ->getForm();

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
     * Export all results of a survey to a csv file
     *
     * @Route("/survey/{id}/result/export", name="survey_export", requirements={"id" = "\d+"})
     *
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function exportSurveyResultAction($id, Request $request){
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

        $results = array();
        $headers = array();
        $respondents = $survey->getRespondents();
        foreach ($respondents as $res){
            $result = array($res->getName(), $res->getTimestamp()->format('Y-m-d H:i'));
            $headers = array("respondent", "timestamp");
            foreach ($res->getAnswers() as $answer){
                $question = $answer->getSurveyQuestion()->getQuestion()->getContent();
                $headers[] = $question;
                if ($answer->getSurveyQuestion()->getQuestion()->getQuestionType() == "text"){
                    $result[] = $answer->getContent();
                }else{
                    $selected_choice = array();
                    foreach ($answer->getChoices() as $choice){
                        $selected_choice[] = $choice->getContent();
                    }
                    $result[] = join("|", $selected_choice);
                }
            }
            $results[] = $result;
        }


        $res = $this->render('survey/surveyResult.csv.twig',
            array(
                'survey'=>$survey,
                'headers'=>$headers,
                'results'=>$results
            )
        );
        $fic = 'survey_'.$id.'_'. \date("Y-m-d").'.csv';
        $res->headers->set('Content-Disposition','attachment; filename="'.$fic.'"');
        $res->headers->set('Content-Type', 'text/csv');
        return $res;

    }


    /**
     * Show a survey results (all results)
     *
     * @Route("/survey/{id}/result", name="survey_show", requirements={"id" = "\d+"})
     *
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function showSurveyResultAction($id, Request $request){
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

        return $this->render('survey/surveyView.html.twig', array('survey'=>$survey));
    }


    /**
     * Show a survey results (all results)
     *
     * @Route("/survey/{id}/result/{respondent_id}", name="survey_single_result", requirements={"id" = "\d+", "respondent_id" = "\d+"})
     *
     * @param $id
     * @param $respondent_id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function showSingleSurveyResultAction($id, $respondent_id, Request $request){
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

        $respondent = $em->getRepository('AppBundle:Respondent')->find($respondent_id);
        if (null === $respondent) {
            throw new NotFoundHttpException("The respondent with id ".$respondent_id." doesn't exist");
        }

        if ($respondent->getSurvey() != $survey){
            $this->addFlash( "danger","The specific respondent ".$respondent_id." doesnt belong to the survey ".$id);
            return $this->redirectToRoute('list_surveys');
        }

        return $this->render('survey/singleSurveyResultView.html.twig', array('survey'=>$survey, 'respondent'=>$respondent));
    }


    /**
     * Public view of a survey
     *
     * @Route("/survey/{id}/view", name="survey_view_public", requirements={"id" = "\d+"})
     *
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
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
                        // Check if the choice belongs to the current question
                        if ($choice->getQuestion() == $question){
                            $answer->setContent("");
                            $answer->addChoice($choice);
                        }
                    }
                }else{
                    $choices = $request->request->get($answer_name);
                    $answer->setContent("");
                    foreach ($choices as $choiceId){
                        $choice = $em->getRepository("AppBundle:Choice")->find($choiceId);
                        if ($choice){
                            // Check if the choice belongs to the current question
                            if ($choice->getQuestion() == $question) {
                                $answer->addChoice($choice);
                            }
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
