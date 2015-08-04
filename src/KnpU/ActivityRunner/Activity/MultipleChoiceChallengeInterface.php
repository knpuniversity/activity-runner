<?php

namespace KnpU\ActivityRunner\Activity;

use KnpU\ActivityRunner\Activity\MultipleChoice\AnswerBuilder;

interface MultipleChoiceChallengeInterface extends ChallengeInterface
{
    /**
     * @param AnswerBuilder $builder
     * @return void
     */
    public function configureAnswers(AnswerBuilder $builder);

    /**
     * @return string
     */
    public function getExplanation();
}
