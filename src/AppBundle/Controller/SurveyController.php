<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Survey;
use AppBundle\Entity\Question;
use AppBundle\Entity\SurveyQuestion;
use AppBundle\Datatables\SurveyDatatable;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route; #DOT NOT UNCOMMENT EVEN IF AN ERROR OCCUR
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

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
            ->add('save', SubmitType::class, array('label' => 'Create Task'))
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
                return $this->redirectToRoute('add_survey');
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
     * @Route("/survey/{id}/delete", name="delete_survey")
     */
    public function deleteSurveyAction(){

    }

    /**
     * Show a survey
     *
     * @Route("/survey/{id}/delete", name="survey_show")
     */
    public function showSurveyAction(){

    }
}
