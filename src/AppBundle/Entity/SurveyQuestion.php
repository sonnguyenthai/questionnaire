<?php

namespace AppBundle\Entity;

/**
 * Class SurveyQuestion
 * @package AppBundle\Entity
 */
class SurveyQuestion
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \DateTime
     */
    private $created_date = 0;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $answers;

    /**
     * @var \AppBundle\Entity\Question
     */
    private $question;

    /**
     * @var \AppBundle\Entity\Survey
     */
    private $survey;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->answers = new \Doctrine\Common\Collections\ArrayCollection();
        $this->created_date = new \DateTime('now');
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
     * Set createdDate
     *
     * @param \DateTime $createdDate
     *
     * @return SurveyQuestion
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
     * Add answer
     *
     * @param \AppBundle\Entity\Answer $answer
     *
     * @return SurveyQuestion
     */
    public function addAnswer(\AppBundle\Entity\Answer $answer)
    {
        $this->answers[] = $answer;

        return $this;
    }

    /**
     * Remove answer
     *
     * @param \AppBundle\Entity\Answer $answer
     */
    public function removeAnswer(\AppBundle\Entity\Answer $answer)
    {
        $this->answers->removeElement($answer);
    }

    /**
     * Get answers
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAnswers()
    {
        return $this->answers;
    }

    /**
     * Set question
     *
     * @param \AppBundle\Entity\Question $question
     *
     * @return SurveyQuestion
     */
    public function setQuestion(\AppBundle\Entity\Question $question = null)
    {
        $this->question = $question;

        return $this;
    }

    /**
     * Get question
     *
     * @return \AppBundle\Entity\Question
     */
    public function getQuestion()
    {
        return $this->question;
    }

    /**
     * @param Survey|null $survey
     * @return $this
     */
    public function setSurvey(\AppBundle\Entity\Survey $survey = null)
    {
        $this->survey = $survey;

        return $this;
    }

    /**
     * Get survey
     *
     * @return \AppBundle\Entity\Survey
     */
    public function getSurvey()
    {
        return $this->survey;
    }
}
