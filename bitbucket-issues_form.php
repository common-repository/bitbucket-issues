<div class="wrap">
    <h2>BitBucket Issues Settings</h2>
    <form name="<?php echo BitbucketIssues::formField("auth")?>" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
        <input type="hidden" name="<?php echo BitbucketIssues::formField("auth")?>" value="Y">

        <h4>Authentication Details</h4>
        <table class="form-table">
            <tbody>
                <?php foreach ( array("username","password") as $field ): ?>
                    <tr valign="top">
                        <th scope="row"><label for="<?php echo BitbucketIssues::formField($field)?>"><?php echo ucwords($field)?>:</label></th>
                        <td>
                            <input type="text" id="<?php echo BitbucketIssues::formField($field)?>" name="<?php echo BitbucketIssues::formField($field)?>" value="<?php echo BitbucketIssues::dbValue($field)?>">
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <p class="submit">  
            <input type="submit" name="<?php echo BitbucketIssues::formField("submit_auth")?>" value="Update Options" />  
        </p>
    </form>        
    
    
    
    <?php 
        $repos = BitbucketIssues::broker()->getRepositories();
        if ( count($repos) > 0 ):
            ?>
            <h2>Repositories</h2>
            <p>Choose which repositories to show issues for. You can choose to show issues for logged in and public users separately.</p>
            
            <form name="<?php echo BitbucketIssues::formField("repos")?>" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
            <input type="hidden" name="<?php echo BitbucketIssues::formField("repos")?>" value="Y">
            <?php function showRepoForm($repos,$ns,$description){ ?>
                    <table class="form-table"> 
                        <tbody>
                            <tr valign="top">
                                <th scope="row"><?php echo $description ?>:</th>
                                <td>
                                    <fieldset>
                                        <legend class="screen-reader-text">Show Issues for the following repositories:</legend>
                                        <input type="checkbox" class="checkall">Check All<br />
                                        <?php 
                                        foreach ( $repos as $repo ): ?>                                            
                                            <input type="checkbox" id="<?php echo BitbucketIssues::$formPrefix.$ns.$repo->slug ?>" name="<?php echo BitbucketIssues::formField($ns."repos")?>[]" value="<?php echo $repo->slug ?>" <?php checked(BitbucketIssues::isRepoSelected($repo->slug, $ns))?>>
                                            <label for="<?php echo BitbucketIssues::$formPrefix.$ns.$repo->slug; ?>"><?php echo $repo->name ?> <span class="description">(<?php if ( $repo->is_private==1 ) { echo "Private"; } else { echo "Public"; }?>)</span></label>
                                            <br />
                                        <?php endforeach; ?>
                                    </fieldset>
                                </td>
                            </tr>
                        </tbody>
                    </table>
            <?php } 

            showRepoForm($repos,"loggedin_","Show these issues to logged in users");
            showRepoForm($repos,"public_","Show these issues to public");

            ?>
                <p class="submit">  
                    <input type="submit" name="<?php echo BitbucketIssues::formField("submit_repos")?>" value="Update Repositories" />  
                </p>                
            </form>        
            
    <?php endif; ?>        
</div>
