<?php
/** @var \WPML\Nav\Presentation\Controller\PageNavigationController $this */
/** @var WPML\Nav\Domain\Navigation\Item $viewObject */
/** @var int $level */

?>
<ul>
	<?php foreach ( $viewObject->getChildItems() as $childItem ) : ?>
		<li class="<?php if ( $childItem->isCurrentPost() ) : ?>selected <?php endif; ?>icl-level-<?php echo $level; ?>">
			<?php if ( ! $childItem->isCurrentPost() ) : ?>
				<a href="<?php echo $childItem->getPermalink(); ?>">
					<span><?php echo $childItem->getTitle() ?></span>
				</a>
			<?php else : ?>
				<span><?php echo $childItem->getTitle() ?></span>
			<?php endif; ?>

			<?php if ( ! $childItem->isMinihome() && !empty( $childItem->getChildItems() ) ) : ?>
				<?php echo $this->render( 'PageNavigation/Sidebar/ChildItems.html.php', $childItem, [ 'level' => $level +1 ] ); ?>
			<?php endif; ?>
		</li>
	<?php endforeach; ?>
</ul>