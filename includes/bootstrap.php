<?php

function renderBootstrapHead(string $basePath = ''): void
{
    $basePath = rtrim($basePath, '/');
    if ($basePath !== '') {
        $basePath .= '/';
    }
?>
  <link rel="stylesheet" href="<?php echo $basePath; ?>assets/css/bootstrap.min.css" />
  <link rel="stylesheet" href="<?php echo $basePath; ?>assets/css/custom.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<?php
}
