<?php

namespace AppBundle\Entity;

/**
 * Question
 */
class Question
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
     * @var string
     */
    private $question_type = 'text';

    /**
     * @var \DateTime
     */
    private $created_date;

    /**
     * @var \DateTime
     */
    private $modified_date;

    /**
     * @var \AppBundle\Entity\User
     */
    private $user;


    public function __construct()
    {
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
     * @return Question
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
     * Set questionType
     *
     * @param string $questionType
     *
     * @return Question
     */
    public function setQuestionType($questionType)
    {
        $this->question_type = $questionType;

        return $this;
    }

    /**
     * Get questionType
     *
     * @return string
     */
    public function getQuestionType()
    {
        return $this->question_type;
    }

    /**
     * Set createdDate
     *
     * @param \DateTime $createdDate
     *
     * @return Question
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
     * @return Question
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
     * Set user
     *
     * @param \AppBundle\Entity\User $user
     *
     * @return Question
     */
    public function setUser(\AppBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \AppBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }
}
