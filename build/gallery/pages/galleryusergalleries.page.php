<?php


//------------------------------------------------------------------------------------
/**
 * page showing latest images and albums of a user
 *
 *
 */

class GalleryUserGalleriesPage extends GalleryUserPage
{
    #[\Override]
    protected function init()
    {
        parent::init();
        $this->page_title = $this->words->getBuffered("GalleryTitleSets");
        $this->addLateLoadScriptFile('build/gallery.js');
    }

    #[\Override]
    protected function teaser() {}

    #[\Override]
    protected function getSubmenuActiveItem()
    {
        return 'overview';
    }

    #[\Override]
    protected function breadcrumbs() {
        $words = $this->words;
        return '<h1><a href="gallery">'.$words->get('Gallery').'</a> &raquo; <a href="gallery/show/user/'.$this->member->Username.'">'.ucfirst((string) $this->member->Username).'</a></h1>';
    }

    #[\Override]
    public function leftSidebar()
    {
        $galleries = $this->galleries;
        $cnt_pictures = $this->cnt_pictures;
        $username = ($member = $this->loggedInMember) ? $member->username : '';
        $loggedInMember = $this->loggedInMember;
        // require SCRIPT_BASE . 'build/gallery/templates/userinfo.php';
    }

    #[\Override]
    protected function column_col3() {
        $member = $this->member;
        $statement = $this->statement;
        $galleries = $this->galleries;
        $words = new MOD_words();
        ?>
        <div class="p-gallery-show">
            <div class="p-gallery-manage__pagehead">
                <div class="p-gallery-manage__pagehead-row">
                    <div class="p-gallery-manage__pagehead-copy">
                        <div class="p-gallery-manage__pagehead-heading">
                            <a href="members/<?= htmlspecialchars((string) $member->Username) ?>"
                               class="p-edit-subpage__back"
                               aria-label="Back to profile">
                                <i class="fas fa-chevron-left" aria-hidden="true"></i>
                            </a>
                            <div>
                                <p class="p-gallery-manage__pagehead-eyebrow"><?= htmlspecialchars((string) $member->Username) ?></p>
                                <h1 class="p-gallery-manage__pagehead-title"><?= $words->get('Gallery') ?></h1>
                            </div>
                        </div>
                    </div>
                    <?php if ($this->myself): ?>
                    <div class="p-gallery-manage__pagehead-actions">
                        <a href="gallery/manage" class="o-button">
                            <i class="fas fa-cog" aria-hidden="true"></i>
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php require SCRIPT_BASE . 'build/gallery/templates/galleries_overview.php'; ?>
        </div>
        <?php
    }

}
