<?php
    require_once 'classes/Config.php';
    $config = Config::getInstance();

    require_once 'classes/Comment_DB.php';
    $comment_db = Comment_DB::getInstance();
    $comment_db->deleteExpiredComments();
    $comments = $comment_db->getComments();

    require_once 'classes/Session.php';
    $session = new Session();

    require_once 'classes/Comment_Post.php';
    $commentPost = new Comment_Post();

    $janRainAuthorized = false;

    try
    {
        $user = $session->getCurrentUser();
        if (!$user) {
            $user = $session->getJanRainUser();
            if ($user) {
                $janRainAuthorized = true;
            }
        }
    }
    catch (Session_Exception $e)
    {
        header('Location: ' . $config->logoutUrl);
        die();
    }

    if ($commentPost->isValid() && $user)
    {
        $comment_db->addComment($user['userId'], $commentPost->commentText);
        header('Location: ' . $config->indexUrl);
        die();
    }

?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8"/>
        <title>VZ-ID demo client</title>
<!--[if IE]>
        <script>
html5elements = ['aside', 'article', 'footer', 'header', 'section'];
for (var i = 0; i < html5elements.length; i++) document.createElement(html5elements[i]);
        </script>
<![endif]-->
        <link rel="shortcut icon" type="image/x-icon" href="favicon.ico"/>
        <link rel="stylesheet" href="default.css"/>
        <link rel="stylesheet" href="http://static.pe.studivz.net/Js/id/v4/library.css"/>
        <script src="http://static.pe.studivz.net/Js/id/v4/library.js"
            data-authority="platform-redirect.vz-modules.net/r"
            data-authorityssl="platform-redirect.vz-modules.net/r"
        ></script>
    </head>
    <body>
        <h1>VZ-ID demo client</h1>
        <article>
            <p>This page demonstrates how to include VZ Login and VZ Sharing into your Web pages.</p>
            <p>For more information, see VZ Developer Wiki: <a href="http://developer.studivz.net/wiki/index.php/VZ-Login">VZ-Login</a> and  <a href="http://developer.studivz.net/wiki/index.php/Sharing">Sharing</a>.</p>

            <section>
                <h2>VZ Login</h2>
                <p>Simplify the registration flow to your site using the two-step VZ Login. <span class="reference">Ⓐ</span> You can request desired user data, which will be provided via the VZ Vcard, already in use for our OpenSocial integration.</p>
                <p>A sample application is the commenting on the right. Once logged in, users may add their comments.</p>
            </section>

            <section>
                <h2>VZ Sharing (VZ Zeigen)</h2>
                <p>Let your users share content from your site with their VZ friends. Using a simple login, users can opt to share the content in their news feed (Buschfunk) or via the VZ messaging system. oEmbed is fully supported, as are thumbnails or your individual text for posts.</p>
                <footer>
                    <script type="vz/share">
url: <?php echo $config->indexUrl . PHP_EOL ?>
description: VZ-ID demo client
                    </script>                    
                </footer>
            </section>

            <section>
                <h3>Sharing via comment box</h3>
                <p>In addition to the VZ Zeigen button you can also provide a comment box, which allows your users to compose individual news feed (Buschfunk) status updates.</p>
                <footer>
                    <script type="vz/feedBox">
                    </script>
                </footer>
            </section>
            
            <section>
                <h3>Sharing individual page elements</h3>
                <p>You can also integrate sharing more deeply with your site interactions by tying it to individual functionalities, in this example to a single comment. See <span class="reference">Ⓑ</span> on the right.</p>
            </section>
            <section>
                <h3>Or use VZ Login with Janrain</h3>
                <p>If you have an JanRain.com account, you can use their simple overlay implementaion.</p>
                <p>
                    <?php if (!$janRainAuthorized): ?>
                    <a class="rpxnow" onclick="return false;" href="https://vziddemo.rpxnow.com/openid/v2/signin?token_url=<?php echo urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ?>">Sign In</a>
                    <?php else: ?>
                    Your are already logged in!
                    <?php endif ?>
                </p>
            </section>
        </article>

        <aside id="commenting">
<?php if ($user): $name = $user['name']; ?>
            <p><span class="reference">Ⓐ</span>
                Commenting as <?php echo htmlspecialchars($name) ?><?php if (mb_strlen($name) > 0 && $name[mb_strlen($name) - 1] != '.') echo '.' ?><br/>
                (If you are not <?php echo htmlspecialchars($name) ?>, <a href="<?php echo $config->logoutUrl ?>">log out</a>.)</p>
            <form action="" method="post">
                <textarea name="commentText" rows="5" autofocus="autofocus" required="required"></textarea>
                <button type="submit">Send</button>
            </form>
<?php else: ?>
            <p>
                <span class="reference">Ⓐ</span>
                <script>
function login(c)
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
callback : login
fields : <?php echo implode(',', $config->requiredFields) . PHP_EOL ?>
                </script>
            </p>
<?php endif ?>

<?php if (empty($comments)): ?>
            <p>No comments yet.</p>
<?php else: ?>
    <?php foreach($comments as $comment): ?>
            <article id="comment<?php echo $comment['commentId'] ?>">
                <header>
                    <time pubdate="pubdate" datetime="<?php echo date("c", $comment['created']) ?>"><?php echo date("Y-m-d H:i", $comment['created']) ?></time>,
                    <?php echo htmlspecialchars($comment['name']) ?> wrote:
                </header>
                <p lang="und"><?php echo nl2br(htmlspecialchars($comment['commentText'])); ?></p>
                <footer>
                    <span class="reference">Ⓑ</span>
                    <script type="vz/share">
url: <?php echo $config->indexUrl . '#comment' . $comment['commentId'] . PHP_EOL ?>
description: <?php echo htmlspecialchars(mb_strimwidth(str_replace(array("\r\n", "\n", "\r"), ' ', $comment['commentText']), 0, 100, '…')) . PHP_EOL ?>
                    </script>
                </footer>
            </article>
    <?php endforeach ?>
<?php endif ?>
        </aside>

    <!-- Janrain -->
<script type="text/javascript">
  var rpxJsHost = (("https:" == document.location.protocol) ? "https://" : "http://static.");
  document.write(unescape("%3Cscript src='" + rpxJsHost +
"rpxnow.com/js/lib/rpx.js' type='text/javascript'%3E%3C/script%3E"));
</script>
<script type="text/javascript">
  RPXNOW.overlay = true;
  RPXNOW.language_preference = 'en';
</script>
    </body>
</html>