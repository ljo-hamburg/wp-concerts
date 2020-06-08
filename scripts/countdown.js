(($) => {
  $(document).ready(() => {
    $(".countdown").each((index, countdown) => {
      const countDownDate = new Date(
        $(countdown).data("date").toString()
      ).getTime();
      const daysElement = $(countdown).find(".days");
      const hoursElement = $(countdown).find(".hours");
      const minutesElement = $(countdown).find(".minutes");
      const secondsElement = $(countdown).find(".seconds");
      const interval = setInterval(() => {
        const now = new Date().getTime();
        const distance = countDownDate - now;

        // Time calculations for days, hours, minutes and seconds
        const days = Math.max(0, Math.floor(distance / (1000 * 60 * 60 * 24)));
        const hours = Math.max(
          0,
          Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60))
        );
        const minutes = Math.max(
          0,
          Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60))
        );
        const seconds = Math.max(
          0,
          Math.floor((distance % (1000 * 60)) / 1000)
        );

        daysElement.html(days);
        hoursElement.html(hours);
        minutesElement.html(minutes);
        secondsElement.html(seconds);

        // If the count down is finished, stop it.
        if (distance < 0) {
          clearInterval(interval);
        }
      }, 1000);
    });
  });
})(jQuery);
