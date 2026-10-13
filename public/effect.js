$(window).on('load', function(){
	$('.loading').fadeOut('fast');
	$('.container').fadeIn('fast');
});
$('document').ready(function(){
		var vw;
        var balloonsArrived = false;
        function arrangeBalloons() {
            var $balloons = $('#birthday-balloons .balloons');
            var count = $balloons.length;
            if (!count) return;
            var width = $(window).width();
            var balloonWidth = Math.min(100, Math.max(26, (width - 20) / count));
            var gap = Math.min(10, Math.max(1, balloonWidth * 0.08));
            var total = count * balloonWidth + (count - 1) * gap;
            var left = Math.max(0, (width - total) / 2);
            $balloons.each(function(i) {
                $(this).stop(true).css({width: balloonWidth, height: balloonWidth * 1.83,
                    backgroundSize: balloonWidth + 'px ' + (balloonWidth * 1.83) + 'px'});
                $(this).find('h2').css({fontSize: Math.max(14, balloonWidth * .5) + 'px'});
                $(this).animate({top: Math.min(240, Math.max(80, $(window).height() * .28)),
                    left: left + i * (balloonWidth + gap)}, 500);
            });
        }
        $(window).resize(function(){ if (balloonsArrived) arrangeBalloons(); });

	$('#turn_on').click(function(){
		$('#bulb_yellow').addClass('bulb-glow-yellow');
		$('#bulb_red').addClass('bulb-glow-red');
		$('#bulb_blue').addClass('bulb-glow-blue');
		$('#bulb_green').addClass('bulb-glow-green');
		$('#bulb_pink').addClass('bulb-glow-pink');
		$('#bulb_orange').addClass('bulb-glow-orange');
		$('body').addClass('peach');
		$(this).fadeOut('slow').delay(5000).promise().done(function(){
			$('#play').fadeIn('slow');
		});
	});
	$('#play').click(function(){
		var audio = $('.song')[0];
        audio.play();
        $('#bulb_yellow').addClass('bulb-glow-yellow-after');
		$('#bulb_red').addClass('bulb-glow-red-after');
		$('#bulb_blue').addClass('bulb-glow-blue-after');
		$('#bulb_green').addClass('bulb-glow-green-after');
		$('#bulb_pink').addClass('bulb-glow-pink-after');
		$('#bulb_orange').addClass('bulb-glow-orange-after');
		$('body').css('backgroud-color','#FFF');
		$('body').addClass('peach-after');
		$(this).fadeOut('slow').delay(6000).promise().done(function(){
			$('#bannar_coming').fadeIn('slow');
		});
	});

	$('#bannar_coming').click(function(){
		$('.bannar').addClass('bannar-come');
		$(this).fadeOut('slow').delay(6000).promise().done(function(){
			$('#balloons_flying').fadeIn('slow');
		});
	});

    function flyBalloon($balloon) {
        if (!$balloon.length || balloonsArrived) return;
        var maxLeft = Math.max(0, $(window).width() - $balloon.outerWidth());
        var maxBottom = Math.max(0, $(window).height() - $balloon.outerHeight());
        $balloon.animate({left: Math.random() * maxLeft,
            bottom: Math.random() * maxBottom}, 7000 + Math.random() * 4000, function() {
            flyBalloon($balloon);
        });
    }
    $('#balloons_flying').click(function(){
        $('.balloon-border').animate({top:-500},8000);
        $('#birthday-balloons .balloons').each(function(i){
            var $balloon = $(this);
            $balloon.addClass(i % 2 === 0 ? 'balloons-rotate-behaviour-one' : 'balloons-rotate-behaviour-two');
            flyBalloon($balloon);
        });
        $(this).fadeOut('slow').delay(5000).promise().done(function(){
            $('#cake_fadein').fadeIn('slow');
        });
    });

	$('#cake_fadein').click(function(){
		$('.cake').fadeIn('slow');
		$(this).fadeOut('slow').delay(3000).promise().done(function(){
			$('#light_candle').fadeIn('slow');
		});
	});

	$('#light_candle').click(function(){
		$('.fuego').fadeIn('slow');
		$(this).fadeOut('slow').promise().done(function(){
			$('#wish_message').fadeIn('slow');
		});
	});

		
    $('#wish_message').click(function(){
        balloonsArrived = true;
        $('#birthday-balloons .balloons').stop(true).removeClass('balloons-rotate-behaviour-one balloons-rotate-behaviour-two');
        arrangeBalloons();
        $('.balloons').css('opacity','0.9');
        $('.balloons h2').fadeIn(3000);
        $(this).fadeOut('slow').delay(3000).promise().done(function(){
            $('#story').fadeIn('slow');
        });
    });

	$('#story').click(function(){
		$(this).fadeOut('slow');
		$('.cake').fadeOut('fast').promise().done(function(){
			$('.message').fadeIn('slow');
		});
		
		var i;

		function msgLoop (i) {
			$("p:nth-child("+i+")").fadeOut('slow').delay(800).promise().done(function(){
			i=i+1;
			$("p:nth-child("+i+")").fadeIn('slow').delay(1000);
			if(i==50){
				$("p:nth-child(49)").fadeOut('slow').promise().done(function () {
					$('.cake').fadeIn('fast');
				});
				
			}
			else{
				msgLoop(i);
			}			

		});
			// body...
		}
		
		msgLoop(0);
		
	});
});




//alert('hello');