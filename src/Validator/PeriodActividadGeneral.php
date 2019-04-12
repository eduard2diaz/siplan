<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class PeriodActividadGeneral extends Constraint
{
    public $message = 'Ya existe una actividad para el periodo %from% - %to%';
    public $service = 'entity.validator.periodactividadgeneral';
    public $em = null;
    public $repositoryMethod = 'findBy';
    public $from;
    public $place;
    public $to;
    public $errorPath = 'from';
    public $ignoreNull = true;

    public function getRequiredOptions()
    {
        return ['from','to','place'];
    }

    /**
     * The validator must be defined as a service with this name.
     *
     * @return string
     */
    public function validatedBy()
    {
        return $this->service;
    }

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

    public function getDefaultOption()
    {
        return 'from';
    }

}
