<?php

/**
 * This file was created by the developers from Tikamoon.

 */

namespace Tikamoon\KlarnaPlugin\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints\NotBlank;
use Tikamoon\KlarnaPlugin\Constants;

/**
 * @author Vincent Notebaert <vnotebaert@kiosc.com>
 */
final class KlarnaGatewayConfigurationType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('environment', ChoiceType::class, [
                'choices' => [
                    'tikamoon.klarna.test' => Constants::BASE_URI_SANDBOX,
                    'tikamoon.klarna.production' => Constants::BASE_URI_LIVE,
                ],
                'label' => 'tikamoon.klarna.environment',
            ])
            ->add('merchant_id', TextType::class, [
                'label' => 'tikamoon.klarna.merchant_id',
                'constraints' => [
                    new NotBlank([
                        'message' => 'tikamoon.klarna.merchant_id.not_blank',
                        'groups' => ['sylius']
                    ])
                ],
            ])
            ->add('username', TextType::class, [
                'label' => 'tikamoon.klarna.username',
                'constraints' => [
                    new NotBlank([
                        'message' => 'tikamoon.klarna.username.not_blank',
                        'groups' => ['sylius']
                    ])
                ],
            ])
            ->add('password', PasswordType::class, [
                'label' => 'tikamoon.klarna.password',
                'constraints' => [
                    new NotBlank([
                        'message' => 'tikamoon.klarna.password.not_blank',
                        'groups' => ['sylius']
                    ])
                ],
            ])
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                $data = $event->getData();
                $data['payum.http_client'] = '@tikamoon.klarna.bridge.klarna_bridge';
                $event->setData($data);
            });
    }
}
