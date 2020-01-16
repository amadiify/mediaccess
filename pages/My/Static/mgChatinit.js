$(document).ready(function(){
    
    var rtc, mgChat;
    var fullname = phpvars.info.firstname + ' ' + phpvars.info.lastname;

    mgChat = $('#mgVideoChat').mgVideoChat({
        wsURL: 'ws://localhost:8080/live-chat?room='+phpvars.orderid+'&accountid='+phpvars.info.accountid+'&fullname='+fullname+'&order='+phpvars.allorders,
        debug : true
    });

    var cached = null;

    $('#mgVideoChat').mgVideoChat('on', 'logged', function(){
        rtc = $('.list-group-item');

        if (rtc.length > 0)
        {
            var list = document.querySelector('.messages.media-list');
            if (list != null)
            {
                rtc.on('click', (e)=>{
                    var list = document.querySelectorAll('.messages.media-list');
                    [].forEach.call(list, function(el, i){
                        if (el != null)
                        {
                            el.innerHTML = list[list.length-2].innerHTML;
                        }
                    });
                });
            }
            else
            {
                rtc.on('click', (e)=>{
                    appendChats();
                });
            }
        }
        else
        {

            $('body').on('connection-ready', ()=>{
                setTimeout(()=>{
                    rtc = $('.list-group-item');
                    rtc.click((e) => {
                        appendChats();
                    });
                },100);
            });
        }
    });

    function appendChats()
    {
        var chats = phpvars.allchats;
        var list = document.querySelector('.messages.media-list');
        if (chats.length > 0)
        {
            var _list = document.querySelectorAll('.messages.media-list');

            if (_list.length > 1)
            {
                [].forEach.call(_list, function(el, i){
                    if (el != null)
                    {
                        el.innerHTML = _list[_list.length-2].innerHTML;
                    }
                });
            }
            else
            {
                list.innerHTML = '';

                chats.map((dd)=>{
                    list.innerHTML += dd;
                });
            }
        }
    }
});