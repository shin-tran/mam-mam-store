<?php extract($data) ?>

<head>
  <meta
    http-equiv="Content-Type"
    content="text/html; charset=utf-8"
  />
  <title><?php echo $title ? "{$title} | Măm Măm Store" : "Măm Măm Store" ?></title>
  <!--begin::Accessibility Meta Tags-->
  <meta
    name="viewport"
    content="width=device-width, initial-scale=1.0, user-scalable=yes"
  />
  <!--end::Accessibility Meta Tags-->
  <!--begin::Primary Meta Tags-->
  <link
    rel="icon"
    type="image/x-icon"
    href="<?php echo _HOST_URL_PUBLIC ?>/favicon.ico"
  />
  <meta
    name="title"
    content="<?php echo $title ? "{$title} | Măm Măm Store" : "Măm Măm Store" ?>"
  />
  <!--end::Primary Meta Tags-->

  <!-- Toastify JS -->
  <link
    rel="stylesheet"
    type="text/css"
    href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css"
  >

  <link
    rel="preload"
    href="<?php echo _HOST_URL_PUBLIC ?>/css/app.css"
    as="style"
  />
  <link
    rel="stylesheet"
    href="<?php echo _HOST_URL_PUBLIC ?>/css/app.css"
  />
</head>
