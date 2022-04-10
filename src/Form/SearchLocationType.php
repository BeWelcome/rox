<?php

namespace App\Form;

use App\Form\CustomDataClass\LocationRequest;
use App\Form\DataTransformer\LocationRequestToLocationTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Traversable;

class SearchLocationType extends AbstractType implements DataMapperInterface
{
    private LocationRequestToLocationTransformer $transformer;

    public function __construct(LocationRequestToLocationTransformer $transformer)
    {
        $this->transformer = $transformer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('fullname', TextType::class, [
                'required' => false,
                'disabled' => $options['expired'],
                'property_path' => 'name',
                'label' => 'trip.leg.location',
                'constraints' => [
                    new NotBlank(null, 'location.none.given'),
                ],
            ])
            ->add('name', HiddenType::class)
            ->add('geoname_id', HiddenType::class)
            ->add('latitude', HiddenType::class)
            ->add('longitude', HiddenType::class)
            ->setDataMapper($this)
        ;

        $builder
            ->addViewTransformer($this->transformer)
        ;
    }

    /** @param LocationRequest|null $viewData */
    public function mapDataToForms($viewData, Traversable $forms): void
    {
        // there is no data yet, so nothing to prepopulate
        if (null === $viewData) {
            return;
        }

        // invalid data type
        if (!$viewData instanceof LocationRequest) {
            throw new UnexpectedTypeException($viewData, LocationRequest::class);
        }

        $forms = iterator_to_array($forms);

        // initialize form field values
        $forms['fullname']->setData($viewData->name);
        $forms['name']->setData($viewData->name);
        $forms['geoname_id']->setData($viewData->geonameId);
        $forms['latitude']->setData($viewData->latitude);
        $forms['longitude']->setData($viewData->longitude);
    }

    public function mapFormsToData(Traversable $forms, &$viewData): void
    {
        $forms = iterator_to_array($forms);

        // as data is passed by reference, overriding it will change it in
        // the form object as well
        // beware of type inconsistency, see caution below
        $viewData = new LocationRequest();
        $viewData->geonameId = $forms['geoname_id']->getData();
        $viewData->latitude = $forms['latitude']->getData();
        $viewData->longitude = $forms['longitude']->getData();
        $viewData->name = $forms['name']->getData();
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'empty_data' => null,
            'data_class' => LocationRequest::class,
            'expired' => false,
            'error_bubbling' => false,
            'error_mapping' => [
                'name' => 'fullname',
                'geoname_id' => 'fullname',
                'latitude' => 'fullname',
                'longitude' => 'fullname',
            ],
        ]);
    }

    public function getBlockPrefix()
    {
        return '';
    }
}
