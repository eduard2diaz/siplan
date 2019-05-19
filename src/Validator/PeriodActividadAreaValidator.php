<?php

namespace App\Validator;

use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class PeriodActividadAreaValidator extends ConstraintValidator
{
    private $registry;

    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    public function validate($value, Constraint $constraint)
    {

        /* @var $constraint App\Validator\Period */
        $pa = PropertyAccess::createPropertyAccessor();

        if (!$constraint instanceof PeriodActividadArea) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__ . '\PeriodActividadArea');
        }

        if ($constraint->em) {
            $em = $this->registry->getManager($constraint->em);
            if (!$em) {
                throw new ConstraintDefinitionException(sprintf('Object manager "%s" does not exist.', $constraint->em));
            }
        } else {
            $em = $this->registry->getManagerForClass(get_class($value));

            if (!$em) {
                throw new ConstraintDefinitionException(sprintf('Unable to find the object manager associated with an entity of class "%s".', get_class($value)));
            }
        }

        $class = $em->getClassMetadata(get_class($value));
        $repository = $em->getRepository(get_class($value));

        if (!is_string($constraint->from)) {
            throw new UnexpectedTypeException($constraint->from, 'string');
        } else
            if (!$class->hasField($constraint->from) && !$class->hasAssociation($constraint->from))
                throw new ConstraintDefinitionException(sprintf('The field "%s" is not mapped by Doctrine, so it cannot be validated for uniqueness.', $constraint->from));


        $fechaInicio = $pa->getValue($value, $constraint->from);


        if (!is_string($constraint->to)) {
            throw new UnexpectedTypeException($constraint->to, 'string');
        } else
            if (!$class->hasField($constraint->to) && !$class->hasAssociation($constraint->to))
                throw new ConstraintDefinitionException(sprintf('The field "%s" is not mapped by Doctrine, so it cannot be validated for uniqueness.', $constraint->to));


        $fechaFin = $pa->getValue($value, $constraint->to);
        $place = $pa->getValue($value, $constraint->place);
        $area = $pa->getValue($value, $constraint->plan)->getArea()->getId();

        $id = $pa->getValue($value, 'id');

        $parameters = [
            'fechainicio' => $fechaInicio,
            'fechafin' => $fechaFin,
            $constraint->place => $place,
            'area' => $area,
        ];

        $entity=$repository->getClassName();
        if (!$id) {
            $cadena = "SELECT COUNT(r) FROM ".$entity." r JOIN r.".$constraint->plan." p JOIN p.area a WHERE a.id= :area AND r.lugar= :".$constraint->place." AND ((:fechainicio <= r.".$constraint->from." AND :fechafin>=r.".$constraint->from.") OR (:fechainicio >= r.".$constraint->from." AND :fechafin<=r.".$constraint->to.") OR( :fechainicio<=r.".$constraint->to." AND :fechafin>=r.".$constraint->to."))";
        } else {
            $cadena = "SELECT COUNT(r) FROM ".$entity." r JOIN r.".$constraint->plan." p JOIN p.area a WHERE a.id= :area AND r.lugar= :".$constraint->place." AND r.id!= :id  AND((:fechainicio <= r.".$constraint->from." AND :fechafin>=r.".$constraint->from.") OR (:fechainicio >= r.".$constraint->from." AND :fechafin<=r.".$constraint->to.") OR( :fechainicio<=r.".$constraint->to." AND :fechafin>=r.".$constraint->to."))";
            $parameters['id'] = $id;
        }

        $consulta = $em->createQuery($cadena);
        $consulta->setParameters($parameters);
        $result = $consulta->getResult();
        if ($result[0][1] > 0) {
            $this->context->buildViolation($constraint->message)
                ->setTranslationDomain('messages')
                ->setParameter('%from%', $fechaInicio->format('d-m-Y'))
                ->setParameter('%to%',  $fechaFin->format('d-m-Y'))
                ->atPath($constraint->from)
                ->addViolation();

        }
    }
}
