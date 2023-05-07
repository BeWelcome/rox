<?php

namespace App\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Contracts\Translation\TranslatorInterface;

class AutoCompleteExtension extends AbstractTypeExtension
{
    private ?TranslatorInterface $translator = null;

    public function __construct(?TranslatorInterface $translator = null)
    {
        $this->translator = $translator;
    }

    public static function getExtendedTypes(): iterable
    {
        return [ChoiceType::class, TextType::class];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        // makes it legal for FileType fields to have an image_property option
        $resolver->setDefaults([
            'autocomplete' => false,
            'close_after_select' => true,
            'allow_options_create' => false,
            'allow_create_on_blur' => false,
            'create_option_text' => 'select.option.create',
            'no_results_text' => 'select.no.results',
            'max_options' => null,
            'max_items' => null,
            'preload' => 'focus',
            'options_as_html' => false,
        ]);

        $resolver->setNormalizer('preload', function ($options, $value) {
            if (\is_bool($value)) {
                $value = $value ? 'true' : 'false';
            }

            return $value;
        });
    }
    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        if (!$options['autocomplete']) {
            $view->vars['uses_autocomplete'] = false;

            return;
        }

        $attr = $view->vars['attr'] ?? [];
        if (!isset($attr['class'])) {
            $attr['class'] = '';
        }
        $attr['class'] .= ' js-tom-select ';

        $values = [];
        if ($options['options_as_html']) {
            $values['options-as-html'] = '';
        }

        if ($options['close_after_select']) {
            $values['close-after-select'] = $options['close_after_select'];
        }

        if ($options['allow_options_create']) {
            $values['create'] = $options['allow_options_create'];
        }

        if ($options['allow_create_on_blur']) {
            $values['create-on-blur'] = $options['allow_create_on_blur'];
        }

        if ($options['max_items']) {
            $values['max-items'] = $options['max_items'];
        }

        if ($options['max_options']) {
            $values['max-options'] = $options['max_options'];
        }

        $values['create-option-text'] = $this->trans($options['create_option_text']);
        $values['no-results-text'] = $this->trans($options['no_results_text']);
        $values['preload'] = $options['preload'];

        foreach ($values as $name => $value) {
            $attr['data-' . $name] = $value;
        }

        $view->vars['uses_autocomplete'] = true;
        $view->vars['attr'] = $attr;
    }

    private function trans(string $message): string
    {
        return $this->translator ? $this->translator->trans($message) : $message;
    }
}
