<?php
/** @var \WPML\Nav\Presentation\Controller\PageNavigationController $this */
/** @var WPML\Nav\Domain\Navigation\Sidebar $viewObject */


echo $viewObject->getHeadingContentPrefix();

if ( ! $viewObject->getRootItem()->isCurrentPost() ) { ?>
    <a href="<?php echo $viewObject->getRootItem()->getPermalink(); ?>">
<?php }

echo $viewObject->getRootItem()->getTitle();

if ( ! $viewObject->getRootItem()->isCurrentPost() ) { ?>
    </a>
<?php }

echo $viewObject->getHeadingContentSuffix();

if( $viewObject->getRootItem() && ! empty( $viewObject->getSections() ) ) { ?>
    <ul class="cms-nav-sidebar">
        <?php foreach ( $viewObject->getSections() as $section ): ?>
        <?php if ( $section->getTitle() ) : ?>
        <li class="cms-nav-sub-section"><?php echo $section->getTitle(); ?></li>
        <?php endif; ?>
        <?php foreach ( $section->getItems() as $item ) : ?>
                <li class="<?php if ( $item->isCurrentPost() ) : ?>selected_page_side <?php endif; ?>icl-level-1">
                    <?php if ( ! $item->isCurrentPost() ) : ?>
                        <a href="<?php echo $item->getPermalink(); ?>">
                    <?php endif ?>
                    <span><?php echo $item->getTitle(); ?></span>
                    <?php if ( ! $item->isCurrentPost() ) : ?>
                       </a>
                    <?php endif;  ?>
                    <?php if ( ! $item->isMinihome() && !empty( $item->getChildItems() ) ) {
                        echo $this->render( 'PageNavigation/Sidebar/ChildItems.html.php', $item, [ 'level' => 2 ] );
                    }
                    ?>
                </li>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </ul>
<?php } ?>