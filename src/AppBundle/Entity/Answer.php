<?php

namespace AppBundle\Entity;


/**
 * Class Answer
 * @package AppBundle\Entity
 */
class Answer
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $content;

    /**
     * @var \DateTime
     */
    private $created_date = 0;

    /**
     * @var \DateTime
     */
    private $modified_date = 0;

    /**
     * @var \AppBundle\Entity\SurveyQuestion
     */
    private $survey_question;

    /**
     * @var \AppBundle\Entity\Respondent
     */
    private $respondent;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $choices;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->choices = new \Doctrine\Common\Collections\ArrayCollection();
        $this->created_date = new \DateTime('now');
        $this->modified_date = new \DateTime('now');
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set content
     *
     * @param string $content
     *
     * @return Answer
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set createdDate
     *
     * @param \DateTime $createdDate
     *
     * @return Answer
     */
    public function setCreatedDate($createdDate)
    {
        $this->created_date = $createdDate;

        return $this;
    }

    /**
     * Get createdDate
     *
     * @return \DateTime
     */
    public function getCreatedDate()
    {
        return $this->created_date;
    }

    /**
     * Set modifiedDate
     *
     * @param \DateTime $modifiedDate
     *
     * @return Answer
     */
    public function setModifiedDate($modifiedDate)
    {
        $this->modified_date = $modifiedDate;

        return $this;
    }

    /**
     * Get modifiedDate
     *
     * @return \DateTime
     */
    public function getModifiedDate()
    {
        return $this->modified_date;
    }

    /**
     * Set surveyQuestion
     *
     * @param \AppBundle\Entity\SurveyQuestion $surveyQuestion
     *
     * @return Answer
     */
    public function setSurveyQuestion(\AppBundle\Entity\SurveyQuestion $surveyQuestion = null)
    {
        $this->survey_question = $surveyQuestion;

        return $this;
    }

    /**
     * Get surveyQuestion
     *
     * @return \AppBundle\Entity\SurveyQuestion
     */
    public function getSurveyQuestion()
    {
        return $this->survey_question;
    }

    /**
     * Set respondent
     *
     * @param \AppBundle\Entity\Respondent $respondent
     *
     * @return Answer
     */
    public function setRespondent(\AppBundle\Entity\Respondent $respondent = null)
    {
        $this->respondent = $respondent;

        return $this;
    }

    /**
     * Get respondent
     *
     * @return \AppBundle\Entity\Respondent
     */
    public function getRespondent()
    {
        return $this->respondent;
    }

    /**
     * Add choice
     *
     * @param \AppBundle\Entity\Choice $choice
     *
     * @return Answer
     */
    public function addChoice(\AppBundle\Entity\Choice $choice)
    {
        $this->choices[] = $choice;

        return $this;
    }

    /**
     * Remove choice
     *
     * @param \AppBundle\Entity\Choice $choice
     */
    public function removeChoice(\AppBundle\Entity\Choice $choice)
    {
        $this->choices->removeElement($choice);
    }

    /**
     * Get choices
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getChoices()
    {
        return $this->choices;
    }
}
