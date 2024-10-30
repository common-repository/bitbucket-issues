<?php
$ns = (is_user_logged_in()) ? "loggedin_" : "public_" ;
$repos = BitbucketIssues::broker()->getRepositories();
function getRepo($repo,$repos) {
    foreach ( $repos as $r ) {
        if ( $r->slug == $repo )
            return $r;
    }
}

foreach ( BitbucketIssues::listRepositories($ns) as $repo ) {
    $repository = getRepo($repo,$repos);
    $issues = BitbucketIssues::broker()->getIssues($repo);
    
    ?>
    <article class="bbi_repository<?php if(count($issues)<1){?> noissues<?php }?>">
        <header>
            <h2><a target="_blank" href="https://bitbucket.org/<?php echo BitbucketIssues::broker()->username?>/<?php echo $repository->slug?>"><?php echo $repository->name ?></a></h2>
        </header>
        <div class="entry-content">
            <?php if (count($issues)<1) { ?>
                <p>No issues :)</p>
            <?php } else { ?>
                
                <ol class="bbi_issue_list">
                <?php foreach ($issues as $issue): ?>
                    <?php 
                        $title = $issue->title;
                        $url = 'https://'.BitbucketIssues::broker()->username.'/'.$repository->slug.'/issue/'.$issue->id;
                        $description = $issue->content;
                    ?>
                    <li class="bbi_issue<?php if ($description=="") {?> nodesc<?php }?>">
                        <p class="title"><a target="_blank" href="<?php echo $url ?>"><?php echo $title; ?></a></p>
                        <p class="description"><?php echo $description ?></p>
                    </li>
                <?php endforeach; ?>
                </ol>
            <?php } ?>
        </div>
    </article>
    <?php
}

?>
