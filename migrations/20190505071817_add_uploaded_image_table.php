<?php


use Rox\Tools\RoxMigration;

class AddUploadedImageTable extends RoxMigration
{
    /**
     * Create table for uploaded image through the ckeditor
     */
    public function change()
    {
        $uploadedImage = $this->table('uploaded_image')
            ->addColumn('filename', 'string', [ 'length' => 100 ])
            ->addColumn('mimetype', 'string', [ 'length' => 100 ])
            ->addColumn('width', 'integer')
            ->addColumn('height', 'integer')
            ->addColumn('created', 'datetime');
        $uploadedImage->create();
    }
}
