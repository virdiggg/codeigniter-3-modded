<!DOCTYPE html>
<html lang="id" dir="ltr">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="robots" content="noindex,nofollow" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta http-equiv="Cache-Control" content="max-age=86400" />
    <title><?= tpl_yield('title', 'Custom Codeigniter 3') ?></title>

    <style>
        body {
            font-family: "Times New Roman", Times, serif;
            margin-left: 1cm;
            margin-right: 1cm;
            line-height: 1.5;
        }
        table {
            font-size: 16px;
        }
        #header {
            text-align: center;
            /* border: 1px solid black; */
        }
        #body {
            padding-top: 20px;
            min-height: 700px;
            /* border: 1px solid red; */
        }
        #footer {
            text-align: center;
            /* border: 1px solid green; */
        }
        #footer table {
            width: 100%;
        }
        img {
            width: 175px;
        }

        @page {
            size: auto;
        }
        @page :left {
            margin-left: 1cm;
            margin-right: 1cm;
        }
        @page :right {
            margin-left: 1cm;
            margin-right: 1cm;
        }
        @page :first {
            margin-top: 1cm
        }
    </style>
    <?= tpl_stack('styles') ?>
</head>

<body>
    <?= tpl_yield('content') ?>

    <script type="text/javascript" src="<?= assets('js/pusher-7.0.6/pusher-with-encryption.min.js') ?>"></script>
    <script type="text/javascript">
        const TEST = 'ok';
    <?php if (!empty($this->config->item('pusher_key'))): ?>
        // Will load pusher when config is set
        const pusher = new Pusher("<?= $this->config->item('pusher_key') ?>", {
            cluster: "<?= $this->config->item('pusher_cluster') ?>",
            encrypted: true
        });
        const channel = pusher.subscribe("<?= $this->config->item('pusher_channel') ?>");

        // Example of binding to a Pusher event and handling the data received
        // This is for all pages that use this wrapper.php layout
        channel.bind('pusher_action', function(data) {
            if (data) {
                // do something
                // showToast(data.message);
                // fetch...
            }
        });
        channel.bind('pusher_action_changed', function(data) {
            if (data) {
                if (typeof data.id == 'undefined') {
                    // do something
                    // showToast(data.message);
                    // fetch...
                    // return;
                }
                // other fetch...
            }
        });
    <?php endif ?>
    </script>
    <?= tpl_stack('scripts') ?>
</body>
</html>