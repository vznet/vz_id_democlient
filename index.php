<?php
    require_once 'classes/Comment_DB.php';
    $comment_db = Comment_DB::getInstance();
    $comments = $comment_db->getComments();

    require_once 'classes/User_DB.php';
    $user_db = User_DB::getInstance();

    require_once 'classes/User.php';
    $user = new User();

    require_once 'classes/Config.php';
    $config = Config::getInstance();

    require_once 'classes/Comment_Post.php';
    $commentPost = new Comment_Post();

    try
    {
        $isLoggedIn = $user->checkLogin(); // $user = $session->getCurrentUser();
    }
    catch (User_Exception $e)
    {
        print_r('not logged in; exception: %1$s', $e);
        header('Location: ' . $config->logoutUrl);
    }

    if ($commentPost->isValid() && $isLoggedIn)
    {
        $comment_db->addComment($user->getId(), $commentPost->commentText);
        header('Location: ' . $config->indexUrl);
    }
    else
    {
        $name = $user->getName();
        if (!$name)
        {
            $name = 'no name';
        }
    }

?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8"/>
        <title>VZ-ID demo client</title>
        <!--[if IE]><script src="ie.js"/></script><![endif]-->
        <link rel="shortcut icon" type="image/x-icon" href="favicon.ico"/>
        <link rel="stylesheet" href="default.css"/>
        <link rel="stylesheet" href="http://static.pe.studivz.net/Js/id/v3/library.css"/>
        <script src="http://static.pe.studivz.net/Js/id/v3/library.js"
            data-authority="platform-redirect.vz-modules.net/r"
            data-authorityssl="platform-redirect.vz-modules.net/r"
        ></script>
    </head>
    <body>
<?php if ($isLoggedIn): $name = $user->getName(); ?>
        <p>Commenting as <?php echo htmlspecialchars($name) ?><?php if (mb_strlen($name) > 0 && $name[mb_strlen($name) - 1] != '.') echo '.' ?> (If you are not <?php echo htmlspecialchars($name) ?>, <a href="<?php echo $config->logoutUrl ?>">log out</a>.)</p>
        <form action="" method="post">
            <textarea name="commentText" rows="5" autofocus="autofocus" required="required"></textarea>
            <button type="submit">Send</button>
        </form>
<?php else: ?>
        <p>
            <script>
function logResponse(c)
{
    if (c.error)
    {
        if (console) console.log(c);
        return;
    }

    var parameters = 'access_token=' + c.access_token;
    parameters += '&user_id=' + c.user_id;
    parameters += '&signature=' + c.signature;
    parameters += '&issued_at=' + c.issued_at;

    document.cookie = '<?php echo $config->cookieKey ?>' + '=' +  encodeURIComponent(parameters);
    document.location.href = '<?php echo $config->indexUrl ?>';
}
            </script>

            <script type="vz/login">
client_id : <?php echo $config->consumerKey . PHP_EOL ?>
redirect_uri : <?php echo $config->redirectUrl . PHP_EOL ?>
callback : logResponse
fields : <?php echo implode(',', $config->requiredFields) . PHP_EOL ?>
            </script>
        </p>
<?php endif ?>

<?php if (empty($comments)): ?>
        <p>No comments yet.</p>
<?php else: ?>
    <?php foreach($comments as $comment): ?>
        <article id="comment<?php echo $comment['commentId'] ?>" lang="und">
            <header>
                <?php echo htmlspecialchars($comment['name']) ?>,
                <time><?php echo date("Y-m-d H:i", $comment['timestamp']) ?></time>
            </header>
            <p><?php echo nl2br(htmlspecialchars($comment['commentText'])); ?></p>
            <footer>
                <script type="vz/share">
url: <?php echo $config->indexUrl . '#comment' . $comment['commentId'] . PHP_EOL ?>
description: <?php echo htmlspecialchars(mb_strimwidth(str_replace(array("\r\n", "\n", "\r"), ' ', $comment['commentText']), 0, 100, '…')) . PHP_EOL ?>
                </script>
            </footer>
        </article>
    <?php endforeach ?>
<?php endif ?>
        <script type="vz/feedBox">
        </script>
    </body>
</html>