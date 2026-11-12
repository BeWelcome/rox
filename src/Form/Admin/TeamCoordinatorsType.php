<?php

namespace App\Form\Admin;

use App\Model\About\VolunteerTeams;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * The coordinators of the teams of Get active: a username per team (suggestions in the template), empty for none.
 */
class TeamCoordinatorsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        foreach (array_keys(VolunteerTeams::TEAMS) as $team) {
            $builder->add($team, TextType::class, [
                'label' => 'getactive' . $team . 'title',
                'required' => false,
                'empty_data' => '',
            ]);
        }
    }
}
