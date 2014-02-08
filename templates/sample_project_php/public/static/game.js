$(function() {
	var el = $('<div class="game" />');
	var targetCount = 0;
	var score = 0;
	$('body').append(el);
	var timeout = setInterval(function() {
		if (targetCount >= config.targetLimit) {
			clearInterval(timeout);
			var scoreEl = $('<div class="score" />');
			scoreEl.text("Score: " + score);
			el.remove();
			$('body').append(scoreEl);
		} else {
			++targetCount;
			var target = createTarget();
			el.append(target.el);
			target.el.css({
				left: Math.random() * (el.width() - target.el.width()),
				top: Math.random() * (el.height() - target.el.height()),
			});
			target.onExplode = function() {
				--targetCount;
				++score;
			};
		}
	}, config.interval);
});
