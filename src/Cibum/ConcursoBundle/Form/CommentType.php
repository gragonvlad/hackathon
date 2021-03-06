<?php

namespace Cibum\ConcursoBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class CommentType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add(
            'content',
            'textarea',
            array(
                'label' => 'Comentario',
            )
        );
    }

    public function getName()
    {
        return 'comment';
    }
}
