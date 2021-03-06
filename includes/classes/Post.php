<?php
class Post
{
    private $user_obj;
    private $con;

    public function __construct($con, $user)
    {
        $this->con = $con;
        $this->user_obj = new User($con, $user);
    }

    public function submitPost($selectedClass, $body, $userId)
    {

        $body = strip_tags($body);
        $body = mysqli_real_escape_string($this->con, $body);

        //Current date and time
        $date_added = date("Y-m-d H:i:s");
        //Get username
        $added_by = $this->user_obj->getUsername();

        //insert post
        $insertPort = mysqli_query($this->con, "INSERT INTO post VALUES('','$date_added','$body', '0', '0')");
        $returned_id = mysqli_insert_id($this->con);

        $relatePost = mysqli_query($this->con, "INSERT INTO postuser VALUES('$returned_id', '$userId', '$selectedClass') ");

        if ($relatePost) {
            echo "<p class='success' style='color:green; text-transform:capitalize;'>Posted!</p>";
        }

    }

    // BACKUP FUNC OF loadPosts !
    public function loadPPosts($limit)
    {

        $userLoggedIn = $this->user_obj->getUsername();

        $str = ""; //String to return
        $data_query = mysqli_query($this->con, "SELECT * FROM post p JOIN postUser pU ON p.postId=pU.postId ORDER BY p.postId DESC");

        if (mysqli_num_rows($data_query) > 0) {

            $num_iterations = 0; //Number of results checked (not necasserily posted)
            $count = 1;

            while ($row = mysqli_fetch_array($data_query)) {
                $id = $row['postId'];
                $body = $row['postBody'];
                $added_by = $row['userId'];
                $date_time = $row['date'];

                // Get the username of added_by
                $addedByUserName = mysqli_query($this->con, "SELECT username FROM regUser where id='$added_by'");

                $addedByUserName = mysqli_fetch_array($addedByUserName);

                $addedByUserName = $addedByUserName['username'];

                $classAddedTo = $row['classroomId'];
                
                //Get classname from the id
                
                $classNameQuery = mysqli_query($this->con, "SELECT className from classrooms WHERE classroomId='$classAddedTo' ORDER BY classroomId DESC LIMIT 1");
                
                $className = mysqli_fetch_array($classNameQuery);
                $className = $className['className'];

                $classTo = "to <a href='class.php?currClass=" . $classAddedTo . "'>" . $className . "</a>";


                //Once 10 posts have been loaded, break
                if ($count > $limit) {
                    break;
                } else {
                    $count++;
                }

                if ($userLoggedIn == $addedByUserName) {
                    $delete_button = "<button class='delete_button btn-danger' id='post$id'> X </button>";
                } else {
                    $delete_button = "";
                }

                $user_details_query = mysqli_query($this->con, "SELECT fName, lName, pictureLink FROM regUser WHERE username='$addedByUserName'");
                $user_row = mysqli_fetch_array($user_details_query);
                $first_name = $user_row['fName'];
                $last_name = $user_row['lName'];
                $profile_pic = $user_row['pictureLink'];

                    ?>
					<script>
						function toggle<?php echo $id; ?>() {

							var target = $(event.target);
							if (!target.is("a")) {
								var element = document.getElementById("toggleComment<?php echo $id; ?>");

								if(element.style.display == "block")
									element.style.display = "none";
								else
									element.style.display = "block";
							}
						}

					</script>
					<?php

                    $comments_check = mysqli_query($this->con, "SELECT * FROM commentPost WHERE postId='$id'");
                    $comments_check_num = mysqli_num_rows($comments_check);

                    //Timeframe
                    $date_time_now = date("Y-m-d H:i:s");
                    $start_date = new DateTime($date_time); //Time of post
                    $end_date = new DateTime($date_time_now); //Current time
                    $interval = $start_date->diff($end_date); //Difference between dates
                    if ($interval->y >= 1) {
                        if ($interval == 1) {
                            $time_message = $interval->y . " year ago";
                        }
                        //1 year ago
                        else {
                            $time_message = $interval->y . " years ago";
                        }
                        //1+ year ago
                    } else if ($interval->m >= 1) {
                        if ($interval->d == 0) {
                            $days = " ago";
                        } else if ($interval->d == 1) {
                            $days = $interval->d . " day ago";
                        } else {
                            $days = $interval->d . " days ago";
                        }

                        if ($interval->m == 1) {
                            $time_message = $interval->m . " month" . $days;
                        } else {
                            $time_message = $interval->m . " months" . $days;
                        }

                    } else if ($interval->d >= 1) {
                        if ($interval->d == 1) {
                            $time_message = "Yesterday";
                        } else {
                            $time_message = $interval->d . " days ago";
                        }
                    } else if ($interval->h >= 1) {
                        if ($interval->h == 1) {
                            $time_message = $interval->h . " hour ago";
                        } else {
                            $time_message = $interval->h . " hours ago";
                        }
                    } else if ($interval->i >= 1) {
                        if ($interval->i == 1) {
                            $time_message = $interval->i . " minute ago";
                        } else {
                            $time_message = $interval->i . " minutes ago";
                        }
                    } else {
                        if ($interval->s < 30) {
                            $time_message = "Just now";
                        } else {
                            $time_message = $interval->s . " seconds ago";
                        }
                    }

                    $str .= "<div class='status_post' onClick='javascript:toggle$id()'>
								<div class='post_profile_pic'>
									<img src='$profile_pic' width='50'>
								</div>

								<div class='posted_by' style='color:#ACACAC;'>
                                    <a class='postInfo' href='profile.php?id=". $added_by ."'> $first_name $last_name </a>
                                    $classTo &nbsp;&nbsp;&nbsp;&nbsp;$time_message
									$delete_button
								</div>
								<div id='post_body'>
									$body
									<br>
									<br>
									<br>
								</div>

								<div class='newsfeedPostOptions'>
									Comments($comments_check_num)&nbsp;&nbsp;&nbsp;
									<iframe allowtransparency='true' style='background: #FFFFFF;' src='like.php?postID=$id' scrolling='no'></iframe>
								</div>

							</div>
							<div class='post_comment' id='toggleComment$id' style='display:none;'>
								<iframe src='commentFrame.php?postID=$id' id='comment_iframe' frameborder='0'></iframe>
							</div>
							<hr>";
                

                ?>
				<script>

					$(document).ready(function() {

						$('#post<?php echo $id; ?>').on('click', function() {
							bootbox.confirm("Are you sure you want to delete this post?", function(result) {

								$.post("includes/form_handlers/delete_post.php?post_id=<?php echo $id; ?>", {result:result});

								if(result)
									location.reload();

							});
						});


					});

				</script>
				<?php

            } //End while loop

        }

        echo $str;
    }

    public function loadPosts($limit)
    {

        $userLoggedIn = $this->user_obj->getUsername();
        $joinedClasses = $this->user_obj->returnClassIDs();

        $str = ""; //String to return
        $data_query = mysqli_query($this->con, "SELECT * FROM post p JOIN postUser pU ON p.postId=pU.postId WHERE classroomId IN (".implode(',',$joinedClasses).") ORDER BY p.postId DESC");

        if (mysqli_num_rows($data_query) > 0) {

            $num_iterations = 0; //Number of results checked (not necasserily posted)
            $count = 1;

            while ($row = mysqli_fetch_array($data_query)) {
                $id = $row['postId'];
                $body = $row['postBody'];
                $added_by = $row['userId'];
                $date_time = $row['date'];

                // Get the username of added_by
                $addedByUserName = mysqli_query($this->con, "SELECT username FROM regUser where id='$added_by'");

                $addedByUserName = mysqli_fetch_array($addedByUserName);

                $addedByUserName = $addedByUserName['username'];

                $classAddedTo = $row['classroomId'];
                
                //Get classname from the id
                
                $classNameQuery = mysqli_query($this->con, "SELECT className from classrooms WHERE classroomId='$classAddedTo' ORDER BY classroomId DESC LIMIT 1");
                
                $className = mysqli_fetch_array($classNameQuery);
                $className = $className['className'];

                $classTo = "to <a href='class.php?currClass=" . $classAddedTo . "'>" . $className . "</a>";


                //Once 10 posts have been loaded, break
                if ($count > $limit) {
                    break;
                } else {
                    $count++;
                }

                if ($userLoggedIn == $addedByUserName) {
                    $delete_button = "<i class='fa fa-times' aria-hidden='true' id='post$id'></i>";
                } else {
                    $delete_button = "";
                }

                $user_details_query = mysqli_query($this->con, "SELECT fName, lName, pictureLink FROM regUser WHERE username='$addedByUserName'");
                $user_row = mysqli_fetch_array($user_details_query);
                $first_name = $user_row['fName'];
                $last_name = $user_row['lName'];
                $profile_pic = $user_row['pictureLink'];

                    ?>
					<script>
						function toggle<?php echo $id; ?>() {

							var target = $(event.target);
							if (!target.is("a")) {
								var element = document.getElementById("toggleComment<?php echo $id; ?>");

								if(element.style.display == "block")
									element.style.display = "none";
								else
									element.style.display = "block";
							}
						}

					</script>
					<?php

                    $comments_check = mysqli_query($this->con, "SELECT * FROM commentPost WHERE postId='$id'");
                    $comments_check_num = mysqli_num_rows($comments_check);

                    //Timeframe
                    $date_time_now = date("Y-m-d H:i:s");
                    $start_date = new DateTime($date_time); //Time of post
                    $end_date = new DateTime($date_time_now); //Current time
                    $interval = $start_date->diff($end_date); //Difference between dates
                    if ($interval->y >= 1) {
                        if ($interval == 1) {
                            $time_message = $interval->y . " year ago";
                        }
                        //1 year ago
                        else {
                            $time_message = $interval->y . " years ago";
                        }
                        //1+ year ago
                    } else if ($interval->m >= 1) {
                        if ($interval->d == 0) {
                            $days = " ago";
                        } else if ($interval->d == 1) {
                            $days = $interval->d . " day ago";
                        } else {
                            $days = $interval->d . " days ago";
                        }

                        if ($interval->m == 1) {
                            $time_message = $interval->m . " month" . $days;
                        } else {
                            $time_message = $interval->m . " months" . $days;
                        }

                    } else if ($interval->d >= 1) {
                        if ($interval->d == 1) {
                            $time_message = "Yesterday";
                        } else {
                            $time_message = $interval->d . " days ago";
                        }
                    } else if ($interval->h >= 1) {
                        if ($interval->h == 1) {
                            $time_message = $interval->h . " hour ago";
                        } else {
                            $time_message = $interval->h . " hours ago";
                        }
                    } else if ($interval->i >= 1) {
                        if ($interval->i == 1) {
                            $time_message = $interval->i . " minute ago";
                        } else {
                            $time_message = $interval->i . " minutes ago";
                        }
                    } else {
                        if ($interval->s < 30) {
                            $time_message = "Just now";
                        } else {
                            $time_message = $interval->s . " seconds ago";
                        }
                    }

                    $str .= "<div class='status_post' onClick='javascript:toggle$id()'>
								<div class='post_profile_pic'>
									<img src='$profile_pic' width='50'>
								</div>

								<div class='posted_by' style='color:#ACACAC;'>
                                    <a class='postInfo' href='profile.php?id=". $added_by ."'> $first_name $last_name </a>
                                    $classTo &nbsp;&nbsp;&nbsp;&nbsp;$time_message
									$delete_button
								</div>
								<div id='post_body'>
									$body
									<br>
									<br>
									<br>
								</div>

								<div class='newsfeedPostOptions'>
									Comments($comments_check_num)&nbsp;&nbsp;&nbsp;
									<iframe allowtransparency='true' style='background: #FFFFFF;' src='like.php?postID=$id' scrolling='no'></iframe>
								</div>

							</div>
							<div class='post_comment' id='toggleComment$id' style='display:none;'>
								<iframe src='commentFrame.php?postID=$id' id='comment_iframe' frameborder='0'></iframe>
							</div>
							<hr>";
                

                ?>
				<script>

					$(document).ready(function() {

						$('#post<?php echo $id; ?>').on('click', function() {
							bootbox.confirm("Are you sure you want to delete this post?", function(result) {

								$.post("includes/form_handlers/delete_post.php?post_id=<?php echo $id; ?>", {result:result});

								if(result)
									location.reload();

							});
						});


					});

				</script>
				<?php

            } //End while loop

        }

        echo $str;
    }

    public function loadClassPosts($limit, $classNm)
    {

        $userLoggedIn = $this->user_obj->getUsername();

        $str = ""; //String to return
        $data_query = mysqli_query($this->con, "SELECT * FROM post p JOIN postUser pU ON p.postId=pU.postId WHERE classroomId='$classNm' ORDER BY p.postId DESC");

        if (mysqli_num_rows($data_query) > 0) {

            $num_iterations = 0; //Number of results checked (not necasserily posted)
            $count = 1;

            while ($row = mysqli_fetch_array($data_query)) {
                $id = $row['postId'];
                $body = $row['postBody'];
                $added_by = $row['userId'];
                $date_time = $row['date'];

                // Get the username of added_by
                $addedByUserName = mysqli_query($this->con, "SELECT username FROM regUser where id='$added_by'");

                $addedByUserName = mysqli_fetch_array($addedByUserName);

                $addedByUserName = $addedByUserName['username'];

                $classAddedTo = $row['classroomId'];
                
                //Get classname from the id
                
                $classNameQuery = mysqli_query($this->con, "SELECT className from classrooms WHERE classroomId='$classAddedTo' ORDER BY classroomId DESC LIMIT 1");
                
                $className = mysqli_fetch_array($classNameQuery);
                $className = $className['className'];

                $classTo = "to <a href='class.php?currClass=" . $classAddedTo . "'>" . $className . "</a>";


                //Once 10 posts have been loaded, break
                if ($count > $limit) {
                    break;
                } else {
                    $count++;
                }

                if ($userLoggedIn == $addedByUserName) {
                    $delete_button = "<i class='fa fa-times' aria-hidden='true' id='post$id'></i>";
                } else {
                    $delete_button = "";
                }

                $user_details_query = mysqli_query($this->con, "SELECT fName, lName, pictureLink FROM regUser WHERE username='$addedByUserName'");
                $user_row = mysqli_fetch_array($user_details_query);
                $first_name = $user_row['fName'];
                $last_name = $user_row['lName'];
                $profile_pic = $user_row['pictureLink'];

                    ?>
					<script>
						function toggle<?php echo $id; ?>() {

							var target = $(event.target);
							if (!target.is("a")) {
								var element = document.getElementById("toggleComment<?php echo $id; ?>");

								if(element.style.display == "block")
									element.style.display = "none";
								else
									element.style.display = "block";
							}
						}

					</script>
					<?php

                    $comments_check = mysqli_query($this->con, "SELECT * FROM commentPost WHERE postId='$id'");
                    $comments_check_num = mysqli_num_rows($comments_check);

                    //Timeframe
                    $date_time_now = date("Y-m-d H:i:s");
                    $start_date = new DateTime($date_time); //Time of post
                    $end_date = new DateTime($date_time_now); //Current time
                    $interval = $start_date->diff($end_date); //Difference between dates
                    if ($interval->y >= 1) {
                        if ($interval == 1) {
                            $time_message = $interval->y . " year ago";
                        }
                        //1 year ago
                        else {
                            $time_message = $interval->y . " years ago";
                        }
                        //1+ year ago
                    } else if ($interval->m >= 1) {
                        if ($interval->d == 0) {
                            $days = " ago";
                        } else if ($interval->d == 1) {
                            $days = $interval->d . " day ago";
                        } else {
                            $days = $interval->d . " days ago";
                        }

                        if ($interval->m == 1) {
                            $time_message = $interval->m . " month" . $days;
                        } else {
                            $time_message = $interval->m . " months" . $days;
                        }

                    } else if ($interval->d >= 1) {
                        if ($interval->d == 1) {
                            $time_message = "Yesterday";
                        } else {
                            $time_message = $interval->d . " days ago";
                        }
                    } else if ($interval->h >= 1) {
                        if ($interval->h == 1) {
                            $time_message = $interval->h . " hour ago";
                        } else {
                            $time_message = $interval->h . " hours ago";
                        }
                    } else if ($interval->i >= 1) {
                        if ($interval->i == 1) {
                            $time_message = $interval->i . " minute ago";
                        } else {
                            $time_message = $interval->i . " minutes ago";
                        }
                    } else {
                        if ($interval->s < 30) {
                            $time_message = "Just now";
                        } else {
                            $time_message = $interval->s . " seconds ago";
                        }
                    }

                    $str .= "<div class='status_post' onClick='javascript:toggle$id()'>
								<div class='post_profile_pic'>
									<img src='$profile_pic' width='50'>
								</div>

								<div class='posted_by' style='color:#ACACAC;'>
                                    <a class='postInfo' href='profile.php?id=". $added_by ."'> $first_name $last_name </a>
                                    $classTo &nbsp;&nbsp;&nbsp;&nbsp;$time_message
									$delete_button
								</div>
								<div id='post_body'>
									$body
									<br>
									<br>
									<br>
								</div>

								<div class='newsfeedPostOptions'>
									Comments($comments_check_num)&nbsp;&nbsp;&nbsp;
									<iframe allowtransparency='true' style='background: #FFFFFF;' src='like.php?postID=$id' scrolling='no'></iframe>
								</div>

							</div>
							<div class='post_comment' id='toggleComment$id' style='display:none;'>
								<iframe src='commentFrame.php?postID=$id' id='comment_iframe' frameborder='0'></iframe>
							</div>
							<hr>";
                

                ?>
				<script>

					$(document).ready(function() {

						$('#post<?php echo $id; ?>').on('click', function() {
							bootbox.confirm("Are you sure you want to delete this post?", function(result) {

								$.post("includes/form_handlers/delete_post.php?post_id=<?php echo $id; ?>", {result:result});

								if(result)
									location.reload();

							});
						});


					});

				</script>
				<?php

            } //End while loop

        }

        echo $str;
    }

// FIX THESE!

    public function loadProfilePosts($data, $limit)
    {
        //Comes from AJAX request
        $page = $data['page'];
        $profileUser = $data['profileUsername'];
        $userLoggedIn = $this->user_obj->getUsername();

        if ($page == 1) {
            $start = 0;
        } else {
            $start = ($page - 1) * $limit;
        }

        $str = ""; //String to return
        $data_query = mysqli_query($this->con, "SELECT * FROM posts WHERE deleted='no' AND ((added_by='$profileUser' AND user_to='none') OR user_to='$profileUser')  ORDER BY id DESC");

        if (mysqli_num_rows($data_query) > 0) {

            $num_iterations = 0; //Number of results checked (not necasserily posted)
            $count = 1;

            while ($row = mysqli_fetch_array($data_query)) {
                $id = $row['id'];
                $body = $row['body'];
                $added_by = $row['added_by'];
                $date_time = $row['date_added'];

                if ($num_iterations++ < $start) {
                    continue;
                }

                //Once 10 posts have been loaded, break
                if ($count > $limit) {
                    break;
                } else {
                    $count++;
                }

                if ($userLoggedIn == $added_by) {
                    $delete_button = "<button class='delete_button btn-danger' id='post$id'>X</button>";
                } else {
                    $delete_button = "";
                }

                $user_details_query = mysqli_query($this->con, "SELECT first_name, last_name, profile_pic FROM users WHERE username='$added_by'");
                $user_row = mysqli_fetch_array($user_details_query);
                $first_name = $user_row['first_name'];
                $last_name = $user_row['last_name'];
                $profile_pic = $user_row['profile_pic'];

                ?>
					<script>
						function toggle<?php echo $id; ?>() {

							var target = $(event.target);
							if (!target.is("a")) {
								var element = document.getElementById("toggleComment<?php echo $id; ?>");

								if(element.style.display == "block")
									element.style.display = "none";
								else
									element.style.display = "block";
							}
						}

					</script>
					<?php

                $comments_check = mysqli_query($this->con, "SELECT * FROM comments WHERE post_id='$id'");
                $comments_check_num = mysqli_num_rows($comments_check);

                //Timeframe
                $date_time_now = date("Y-m-d H:i:s");
                $start_date = new DateTime($date_time); //Time of post
                $end_date = new DateTime($date_time_now); //Current time
                $interval = $start_date->diff($end_date); //Difference between dates
                if ($interval->y >= 1) {
                    if ($interval == 1) {
                        $time_message = $interval->y . " year ago";
                    }
                    //1 year ago
                    else {
                        $time_message = $interval->y . " years ago";
                    }
                    //1+ year ago
                } else if ($interval->m >= 1) {
                    if ($interval->d == 0) {
                        $days = " ago";
                    } else if ($interval->d == 1) {
                        $days = $interval->d . " day ago";
                    } else {
                        $days = $interval->d . " days ago";
                    }

                    if ($interval->m == 1) {
                        $time_message = $interval->m . " month" . $days;
                    } else {
                        $time_message = $interval->m . " months" . $days;
                    }

                } else if ($interval->d >= 1) {
                    if ($interval->d == 1) {
                        $time_message = "Yesterday";
                    } else {
                        $time_message = $interval->d . " days ago";
                    }
                } else if ($interval->h >= 1) {
                    if ($interval->h == 1) {
                        $time_message = $interval->h . " hour ago";
                    } else {
                        $time_message = $interval->h . " hours ago";
                    }
                } else if ($interval->i >= 1) {
                    if ($interval->i == 1) {
                        $time_message = $interval->i . " minute ago";
                    } else {
                        $time_message = $interval->i . " minutes ago";
                    }
                } else {
                    if ($interval->s < 30) {
                        $time_message = "Just now";
                    } else {
                        $time_message = $interval->s . " seconds ago";
                    }
                }

                $str .= "<div class='status_post' onClick='javascript:toggle$id()'>
								<div class='post_profile_pic'>
									<img src='$profile_pic' width='50'>
								</div>

								<div class='posted_by' style='color:#ACACAC;'>
									<a href='$added_by'> $first_name $last_name </a> &nbsp;&nbsp;&nbsp;&nbsp;$time_message
									$delete_button
								</div>
								<div id='post_body'>
									$body
									<br>
									<br>
									<br>
								</div>

								<div class='newsfeedPostOptions'>
									Comments($comments_check_num)&nbsp;&nbsp;&nbsp;
									<iframe src='like.php?post_id=$id' scrolling='no'></iframe>
								</div>

							</div>
							<div class='post_comment' id='toggleComment$id' style='display:none;'>
								<iframe src='comment_frame.php?post_id=$id' id='comment_iframe' frameborder='0'></iframe>
							</div>
							<hr>";

                ?>
				<script>

					$(document).ready(function() {

						$('#post<?php echo $id; ?>').on('click', function() {
							bootbox.confirm("Are you sure you want to delete this post?", function(result) {

								$.post("includes/form_handlers/delete_post.php?post_id=<?php echo $id; ?>", {result:result});

								if(result)
									location.reload();

							});
						});

					});

				</script>
				<?php

            } //End while loop

            if ($count > $limit) {
                $str .= "<input type='hidden' class='nextPage' value='" . ($page + 1) . "'>
							<input type='hidden' class='noMorePosts' value='false'>";
            } else {
                $str .= "<input type='hidden' class='noMorePosts' value='true'><p style='text-align: centre;'> No more posts to show! </p>";
            }

        }

        echo $str;

    }

    public function getSinglePost($post_id)
    {
        $userLoggedIn = $this->user_obj->getUsername();

        $opened_query = mysqli_query($this->con, "UPDATE notifications SET open='yes' WHERE user_to='$userLoggedIn' AND link LIKE '$=$post_id' ");

        $str = ""; //String to return
        $data_query = mysqli_query($this->con, "SELECT * FROM posts WHERE deleted='no' AND id='$post_id'");

        if (mysqli_num_rows($data_query) > 0) {

            $num_iterations = 0; //Number of results checked (not necasserily posted)
            $count = 1;

            $row = mysqli_fetch_array($data_query);
            $id = $row['id'];
            $body = $row['body'];
            $added_by = $row['added_by'];
            $date_time = $row['date_added'];

            //Prepare user_to string so it can be included even if not posted to a user
            if ($row['user_to'] == "none") {
                $user_to = "";
            } else {
                $user_to_obj = new User($this->con, $row['user_to']);
                $user_to_name = $user_to_obj->getFirstAndLastName();
                $user_to = "to <a href='" . $row['user_to'] . "'>" . $user_to_name . "</a>";
            }

            //Check if user who posted, has their account closed
            $added_by_obj = new User($this->con, $added_by);
            if ($added_by_obj->isClosed()) {
                return;
            }

            $user_logged_obj = new User($this->con, $userLoggedIn);
            if ($user_logged_obj->isFriend($added_by)) {

                if ($userLoggedIn == $added_by) {
                    $delete_button = "<button class='delete_button btn-danger' id='post$id'>X</button>";
                } else {
                    $delete_button = "";
                }

                $user_details_query = mysqli_query($this->con, "SELECT first_name, last_name, profile_pic FROM users WHERE username='$added_by'");
                $user_row = mysqli_fetch_array($user_details_query);
                $first_name = $user_row['first_name'];
                $last_name = $user_row['last_name'];
                $profile_pic = $user_row['profile_pic'];

                ?>
					<script>
						function toggle<?php echo $id; ?>() {

							var target = $(event.target);
							if (!target.is("a")) {
								var element = document.getElementById("toggleComment<?php echo $id; ?>");

								if(element.style.display == "block")
									element.style.display = "none";
								else
									element.style.display = "block";
							}
						}

					</script>
					<?php

                $comments_check = mysqli_query($this->con, "SELECT * FROM comments WHERE post_id='$id'");
                $comments_check_num = mysqli_num_rows($comments_check);

                //Timeframe
                $date_time_now = date("Y-m-d H:i:s");
                $start_date = new DateTime($date_time); //Time of post
                $end_date = new DateTime($date_time_now); //Current time
                $interval = $start_date->diff($end_date); //Difference between dates
                if ($interval->y >= 1) {
                    if ($interval == 1) {
                        $time_message = $interval->y . " year ago";
                    }
                    //1 year ago
                    else {
                        $time_message = $interval->y . " years ago";
                    }
                    //1+ year ago
                } else if ($interval->m >= 1) {
                    if ($interval->d == 0) {
                        $days = " ago";
                    } else if ($interval->d == 1) {
                        $days = $interval->d . " day ago";
                    } else {
                        $days = $interval->d . " days ago";
                    }

                    if ($interval->m == 1) {
                        $time_message = $interval->m . " month" . $days;
                    } else {
                        $time_message = $interval->m . " months" . $days;
                    }

                } else if ($interval->d >= 1) {
                    if ($interval->d == 1) {
                        $time_message = "Yesterday";
                    } else {
                        $time_message = $interval->d . " days ago";
                    }
                } else if ($interval->h >= 1) {
                    if ($interval->h == 1) {
                        $time_message = $interval->h . " hour ago";
                    } else {
                        $time_message = $interval->h . " hours ago";
                    }
                } else if ($interval->i >= 1) {
                    if ($interval->i == 1) {
                        $time_message = $interval->i . " minute ago";
                    } else {
                        $time_message = $interval->i . " minutes ago";
                    }
                } else {
                    if ($interval->s < 30) {
                        $time_message = "Just now";
                    } else {
                        $time_message = $interval->s . " seconds ago";
                    }
                }

                $str .= "<div class='status_post' onClick='javascript:toggle$id()'>
								<div class='post_profile_pic'>
									<img src='$profile_pic' width='50'>
								</div>

								<div class='posted_by' style='color:#ACACAC;'>
									<a href='$added_by'> $first_name $last_name </a> $user_to &nbsp;&nbsp;&nbsp;&nbsp;$time_message
									$delete_button
								</div>
								<div id='post_body'>
									$body
									<br>
									<br>
									<br>
								</div>

								<div class='newsfeedPostOptions'>
									Comments($comments_check_num)&nbsp;&nbsp;&nbsp;
									<iframe src='like.php?post_id=$id' scrolling='no'></iframe>
								</div>

							</div>
							<div class='post_comment' id='toggleComment$id' style='display:none;'>
								<iframe src='comment_frame.php?post_id=$id' id='comment_iframe' frameborder='0'></iframe>
							</div>
							<hr>";
                ?>
				<script>

					$(document).ready(function() {

						$('#post<?php echo $id; ?>').on('click', function() {
							bootbox.confirm("Are you sure you want to delete this post?", function(result) {

								$.post("includes/form_handlers/delete_post.php?post_id=<?php echo $id; ?>", {result:result});

								if(result)
									location.reload();

							});
						});

					});

				</script>
				<?php

            } else {
                echo "<p>You can not see this post because you are not friends with this user!</p>";
                return;
            }

        } else {
            echo "<p>No post found. If you clicked a link, it may be broken. </p>";
            return;
        }

        echo $str;

    }

}

?>