<?php
?>
  </main>

  </div>
  </div>

  <footer class="footer book-footer">
    <?php
    $fc1 = $site->bookfooter_col1();
    $fc2 = $site->bookfooter_col2();
    $fc3 = $site->bookfooter_col3();
    $hasFooterCols = $fc1->isNotEmpty() || $fc2->isNotEmpty() || $fc3->isNotEmpty();
    ?>
    <div class="book-footer__inner<?= $hasFooterCols ? ' book-footer__inner--cols' : '' ?>">
      <?php if ($hasFooterCols): ?>
      <div class="book-footer__col book-footer__col--1 book-prose">
        <?= $fc1->isNotEmpty() ? $fc1->toBlocks()->toHtml() : '' ?>
      </div>
      <div class="book-footer__col book-footer__col--2 book-prose">
        <?= $fc2->isNotEmpty() ? $fc2->toBlocks()->toHtml() : '' ?>
      </div>
      <div class="book-footer__col book-footer__col--3 book-prose">
        <?= $fc3->isNotEmpty() ? $fc3->toBlocks()->toHtml() : '' ?>
      </div>
      <?php else: ?>
      <p class="book-footer__meta book-footer__col book-footer__col--1"><?= esc('Publication numérique') ?> · <?= esc(date('Y')) ?></p>
      <?php endif ?>
    </div>
  </footer>

  <?php snippet('book/search-index') ?>
  <?= js('assets/js/book.js') ?>

</body>
</html>
