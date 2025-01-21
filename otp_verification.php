<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.14.0/css/all.min.css"
        integrity="sha512-1PKOgIY59xJ8Co8+NE6FZ+LOAZKjy+KY8iq0G4B3CyeY6wYHN3yt9PW0XpSriVlkMXe40PTKnXrLnZ9+fkDaog=="
        crossorigin="anonymous" />

    <style>
        body {
        margin: 0;
        background: #0f172a;
        height: 100vh;
        color: #fff;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
        overflow: hidden;
        }
        .timer_container{
            width: 100%;
            display: flex;
        }
        #timer{
            font-size: 40px;
            font-weight: 600;
            color: #6366f1;
            margin: 20px;
        }
        .container{
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            height: 70%;
        }

        .otp-field {
        display: flex;
        }

        .otp-field input {
        width: 24px;
        font-size: 32px;
        padding: 10px;
        text-align: center;
        border-radius: 5px;
        margin: 2px;
        border: 2px solid #2a2f3e;
        background: #21232d;
        font-weight: bold;
        color: #fff;
        outline: none;
        transition: all 0.1s;
        }

        .otp-field input:focus {
        border: 2px solid #6366f1;
        box-shadow: 0 0 2px 2px #4e46e593;
        }

        .disabled {
        opacity: 0.5;
        }

        .space {
        margin-right: 1rem !important;
        }
        .links{
            color: #6366f1;
            cursor: pointer;

        }
    </style>

    <title>OTP Field Form - Coding Torque</title>
</head>

<body>
    <div class="timer_container">
        <div id="timer">02:00</div>
        
        <script>
         
        </script>
    </div>
    <div class="container">
        <p>OTP sent on ....12@gmail.com</p> <!-- Display the email address where OTP is sent from the backend -->
        <h1>Enter OTP</h1>
        <div class="otp-field">
            <input type="text" maxlength="1" />
            <input type="text" maxlength="1" />
            <input class="space" type="text" maxlength="1" />
            <input type="text" maxlength="1" />
            <input type="text" maxlength="1" />
            <input type="text" maxlength="1" />

            
        </div>

        <!-- didn't get an otp? -->
        <div class="" style="margin-top: 20px;">
            <span>Didn't get an OTP</span>
            <a class="links"> Resend?</a> <!-- Resend the OTP -->
        </div>
    </div>

    <script>

        // Timer for OTP
        function startTimer(duration, display) {
            let timer = duration, minutes, seconds;
            let countdown = setInterval(function () {
                minutes = parseInt(timer / 60, 10);
                seconds = parseInt(timer % 60, 10);

                minutes = minutes < 10 ? "0" + minutes : minutes;
                seconds = seconds < 10 ? "0" + seconds : seconds;

                display.textContent = minutes + ":" + seconds;

                if (--timer < 0) {
                    clearInterval(countdown);
                    display.textContent = "00:00";
                }
            }, 1000);
        }

        window.onload = function () {
            let twoMinutes = 120,
                display = document.querySelector('#timer');
            startTimer(twoMinutes, display);
        };










        const inputs = document.querySelectorAll(".otp-field input");
        inputs.forEach((input, index) => {
            input.dataset.index = index;
            input.addEventListener("keyup", handleOtp);
            input.addEventListener("paste", handleOnPasteOtp);
        });

        function handleOtp(e) {
            const input = e.target;
            let value = input.value;
            let isValidInput = value.match(/[0-9a-z]/gi);
            input.value = "";
            input.value = isValidInput ? value[0] : "";

            let fieldIndex = input.dataset.index;
            if (fieldIndex < inputs.length - 1 && isValidInput) {
                input.nextElementSibling.focus();
            }

            if (e.key === "Backspace" && fieldIndex > 0) {
                input.previousElementSibling.focus();
            }

            if (fieldIndex == inputs.length - 1 && isValidInput) {
                submit();
            }
        }

        function handleOnPasteOtp(e) {
            const data = e.clipboardData.getData("text");
            const value = data.split("");
            if (value.length === inputs.length) {
                inputs.forEach((input, index) => (input.value = value[index]));
                submit();
            }
        }

        function submit() {
            console.log("Submitting...");
            let otp = "";
            inputs.forEach((input) => {
                otp += input.value;
                input.disabled = true;
                input.classList.add("disabled");
            });
            console.log(otp);

            // immediately the user enters the complete input it will be disabled and the backend will collect the code
            // now the backend will collect the code the user enter after complete input and verify it with the code sent to the user

            // if the code is correct the user will be redirected to the next page
            // if the code is incorrect the user will be prompted to enter the code again

        }
    </script>

</body>

</html>