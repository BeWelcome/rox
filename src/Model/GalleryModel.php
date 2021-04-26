<?php

namespace App\Model;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class GalleryModel
{
    private ValidatorInterface $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    public function checkUploadedImage($image): ConstraintViolationListInterface
    {
        // Create Image constraint to check if uploaded file is an image and not something else

        $constraint = new Image([
            'maxSize' => UploadedFile::getMaxFilesize(),
            'mimeTypes' => ['image/jpeg', 'image/png', 'image/gif'],
            'mimeTypesMessage' => 'upload.error.not_supported',
        ]);

        $violations = $this->validator->validate($image, $constraint);

        return $violations;
    }
}
