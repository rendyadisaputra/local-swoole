var SocketConnection;
var curXY = [0, 5];
var defaultAdder = 15;
var adder = defaultAdder;
var pause = false;

window.onfocus = () => {
    // pause = false;
}
window.onblur = () => {
    // pause = true;
}
players = { count: 0 };

function createNewPlayer(parse) {
    let new1 = $("<div id=\"" + parse['player'] + "\" class=\"block\"></div>")

    $("body").append(new1);
    players[parse['player']] = 1;
    players.count += 1;
}

$(function () {
    "use strict";
    // for better performance - to avoid searching in DOM
    var content = $('#content');
    var input = $('#input');
    var status = $('#status');



    // my name sent to the server
    var myName = false;
    // if user is running mozilla then use it's built-in WebSocket
    window.WebSocket = window.WebSocket || window.MozWebSocket;
    // if browser doesn't support WebSocket, just show
    // some notification and exit
    if (!window.WebSocket) {
        content.html($('<p>',
            { text: 'Sorry, but your browser doesn\'t support WebSocket.' }
        ));
        input.hide();
        $('span').hide();
        return;
    }

    $(".pause").click(()=>{
        pause = !pause;

    })
    // open connection
    // console.log("location", window.location);
    const urlParams = new URLSearchParams(window.location.search);
    var token = 'token02';

    if (urlParams.get("token")) {
        token = urlParams.get("token");
    }

    if (typeof players[token] == 'undefined') {
        createNewPlayer({ player: token, xy: curXY });
    }

    SocketConnection = new WebSocket('wss://local.wshost.com/wsocket/api/abcdefgh/' + token);
    SocketConnection.onopen = function () {
        // first we want users to enter their names
        input.removeAttr('disabled');
        status.text('Choose name:');
    };
    SocketConnection.onerror = function (error) {
        // just in there were some problems with connection...
        content.html($('<p>', {
            text: 'Sorry, but there\'s some problem with your '
                + 'connection or the server is down.'
        }));
    };

    // most important part - incoming messages
    SocketConnection.onmessage = function (message) {
        let parse = JSON.parse(message.data);

        if (typeof parse['xy'] != 'undefined') {
            if (typeof players[parse['player']] == 'undefined') {
                createNewPlayer(parse);
            }
            // console.log(parse['xy']);
            $("#" + parse['player']).css({ left: parse['xy'][0], top: parse['xy'][1], });
        }
    };
    /**
     * Send message when user presses Enter key
     */


    setInterval(() => {
        if (pause) {
            return false;
        }
        curXY[0] += adder;
        curXY[1] += adder;
        if (curXY[1] > 450) {
            adder = -1 * defaultAdder;
        } if (curXY[1] < 0) {
            adder = defaultAdder;
        }
        $("#" + token).css({ left: curXY[0], top: curXY[1] , "z-index": 99});
        var msg = JSON.stringify({
            xy: curXY,
            player: token
        });
        SocketConnection.send(msg);
    }, 120)

    input.keydown(function (e) {
        if (e.keyCode === 13) {
            var msg = $(this).val();
            if (!msg) {
                return;
            }
            // send the message as an ordinary text
            connection.send(msg);
            $(this).val('');
            // disable the input field to make the user wait until server
            // sends back response
            input.attr('disabled', 'disabled');
            // we know that the first message sent from a user their name
            if (myName === false) {
                myName = msg;
            }
        }
    });
    /**
     * This method is optional. If the server wasn't able to
     * respond to the in 3 seconds then show some error message 
     * to notify the user that something is wrong.
     */
    setInterval(function () {
        if (SocketConnection.readyState !== 1) {
            status.text('Error');
            input.attr('disabled', 'disabled').val(
                'Unable to communicate with the WebSocket server.');
        }
    }, 3000);
    /**
     * Add message to the chat window
     */
    function addMessage(author, message, color, dt) {
        content.append('<p><span style="color:' + color + '">'
            + author + '</span> @ ' + (dt.getHours() < 10 ? '0'
                + dt.getHours() : dt.getHours()) + ':'
            + (dt.getMinutes() < 10
                ? '0' + dt.getMinutes() : dt.getMinutes())
            + ': ' + message + '</p>');
    }
});