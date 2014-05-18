//jQuery.noConflict();

function hidelogin() {
	//document.getElementById('login').style.visibility = 'hidden';
	$('div.login').fadeOut();
}

var blink = function() {
    $('#beta').toggle();
};

function newblog() {
    //if ( document.getElementById('newblog').style.visibility == 'hidden') {
        //document.getElementById('newblog').style.visibility = 'visible';
        $('#newblog').fadeIn('slow');
    //} else {
    //    document.getElementById('newblog').style.visibility = 'hidden';
    //}
}

function saveBlog() {
     $.ajax({
        type: "GET",
        url: "ajax/blog.php",
        dataType: "text",
        //data: $('input').serialize(),
        data: $('#write').serialize(),
        success: function(msg) {
            alert(msg);
        },
        error: function(msg) {
            alert("ERROR" + msg);
        },
        complete: function(msg) {
            alert(msg);
        }
     })
     return;
}

jQuery(document).ready(function($) {
        setInterval(blink, 1000);
        
        $('div.blog').dblclick(function() {
            //alert('dlb');
            $('div.blog').html("<textarea cols=100 rows=7>" + $('div.hidden').html());
            $('div.hidden').html("");
        });
        
        $('html').click(function(e) {
            if ($(e.target).attr('id') === 'blog') {
                //alert('CLICK!');
            } else {
                //alert('NO CLICK!');
                if ($('div.hidden').html() == '') {
                    $('div.blog').html('<br /><br /><center><h1>Your item has been saved!</h1></center>');
                }
            }
        });
        
        $('div.blog').blur(function() {
            alert('OUT');
        });


	$('header.img').hide().each(function(i) {
		$(this).delay(i * 500).fadeIn();
	});
	
	$('div.page_fixedwidth').hide().each(function(i) {
		//$(this).delay(i * 1000).fadeIn();
                $(this).delay(i * 1000).slideDown(1000);
	});

        $('div.comment').hide().each(function(i) {
            $(this).delay(i * 700).slideDown(1000);
        });
		
	$('div.reply').hide().each(function(i) {
		//$(this).delay(i * 500).fadeIn();
                $(this).delay(i * 700).slideDown(1000);
	});
	
	$(function() {
            // logout
            if (document.getElementById('login').innerHTML == 'You have been logged out.') {
                $.ajax({
                    type: "GET",
                    url: "ajax/login.php",
                    dataType: "text",
                    data: "logout=true",
                    success: function(msg) {
                        alert(msg);
                    },
                    error: function(msg) {
                        alert("ERROR" + msg);
                    },
                    complete: function(msg) {
                        alert(msg);
                    }
                })
                return;
            }
                var olddata = $('input').serialize();

                $('input').keyup(function() {
			if ($('input').serialize() != olddata) {
                            olddata = $('input').serialize();
                            $.ajax({
                               type: "GET",
                               url: "ajax/login.php",
                               dataType: "text",
                               data: olddata,
                               success: function(msg) {
                                   if (msg == 'EPIC FAIL!OK!') {
                                       $('#login').fadeOut(500);
                                       location.reload(); // refresh the page
                                   }
                               }
                            })
			}
		});		
	});
});

function addfriend(friendid) {
	if ( document.getElementById('send'))
		document.getElementById('send').style.visibility = 'hidden';
	else
		alert("ERROR accessing send");

	if ( document.getElementById('sent'))
		document.getElementById('sent').style.visibility = 'visible';
	else
		alert("ERROR accessing sent");
}



function login(hide) {
	if ( document.getElementById('login')) {
		// toggle
		if ( document.getElementById('login').style.visibility == 'hidden' && hide == false) {
                        // but only show this if we're not already logged in.
                        if (document.getElementById('loggeduser').innerHTML == '<b>Guest</b>') {
                            document.getElementById('login').style.visibility = 'visible';
                            document.forms[0].username.focus();
                        } else {
                            //document.getElementById('login').innerHTML = 'You have been logged out.';
                            //document.getElementById('login').style.visibility = 'visible';
                        }
		} else {
                        /*if ( document.getElementById('login').innerHTML != 'You have been logged out.') {
                                // only hide if the box is empty!
                                if ( document.forms[0].username.value.length == 0 && document.forms[0].password.value.length == 0)
                                        document.getElementById('login').style.visibility = 'hidden';
                        }*/
		}
	} else {
		alert("ERROR accessing login");
	}
}

function clock() {
        var heartbeat = document.getElementById ? document.getElementById("heartbeat") : document.all.heartbeat

        var months = new Array(13);
        months[1]	= "January";
        months[2]	= "February";
        months[3]	= "March";
        months[4]	= "April";
        months[5]	= "May";
        months[6]	= "June";
        months[7]	= "July";
        months[8]	= "August";
        months[9]	= "September";
        months[10]	= "October";
        months[11]	= "November";
        months[12]	= "December";

        var Digital 	= new Date()
        var hours  		= Digital.getHours()
        var minutes		= Digital.getMinutes()
        var seconds		= Digital.getSeconds()
        var tmonth	= months[Digital.getMonth() + 1];
        var	date	= Digital.getDate();
        var	year	= Digital.getYear();

        if (minutes < 10) minutes = "0" + minutes
        if (seconds < 10) seconds = "0" + seconds
        if (year < 2000) year = year + 1900

        heartbeat.innerHTML = hours + ":" + minutes + ":" + seconds + " " + tmonth + " " + date + ", " + year
        setTimeout("clock()", 1000)
}

window.onload = clock