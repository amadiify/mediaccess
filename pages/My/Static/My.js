// handle file upload
var fileupload = document.querySelectorAll('*[data-file]');

if (fileupload.length > 0)
{
    [].forEach.call(fileupload, function(ele){
        // create file handle
        var file = document.createElement('input');
        file.type = 'file';
        file.name = ele.getAttribute('data-file');
        file.style.display = 'none';
        if (ele.hasAttribute('data-accept'))
        {
            file.setAttribute('accept', ele.getAttribute('data-accept'));
        }
        ele.appendChild(file);
        // define first row
        var row = document.createElement('div');
        row.className = 'data-file-row';
        var fa = document.createElement('span'), text = document.createElement('span');
        fa.className = 'fa fa-plus';
        row.appendChild(fa);
        text.textContent = ele.getAttribute('data-text');
        row.appendChild(text);
        ele.appendChild(row);

        ele.addEventListener('click', function(){
            file.click();

            file.addEventListener('change', function(){
                if (this.files.length > 0)
                {
                    ele.classList.add('file-changed');
                }
                else
                {
                    ele.classList.remove('file-changed');
                }
            });
        });
    });
}

// toggle avaliability
var avaliability = document.querySelector('#toggleAvalibility');
if (avaliability != null)
{
    // get accountid
    var id = avaliability.getAttribute('data-accountid');

    function avaliabilityChanged(ev)
    {
        var code = '1';
        switch(ev.checked)
        {
            case true:
                code = '1';
            break;

            case false:
                code = '0';
            break;
        }

        // send http 
        $http.get($url+'my/home/switch/'+id+'/'+code);
    }
}

var form = document.querySelector('.default-form');
if (form != null)
{
    form.addEventListener('keypress', function(ev){
    if (ev.target.parentNode.className == 'bootstrap-tagsinput')
    {
        if (ev.key == 'Enter' || ev.keyCode == 16 || ev.char == 'Enter' || ev.charCode == 16)
        {
            ev.preventDefault();
        }
    }
});
}

// make tr clickable
var trhref = document.querySelectorAll('tr[data-href]');
if (trhref.length > 0)
{
    [].forEach.call(trhref, function(e){
        e.addEventListener('click', function(){
            var href = e.getAttribute("data-href");
            if (!e.hasAttribute('data-target'))
            {
                window.open($url + href, '_self');
            }
        });
    });
}

// preview panel for drugs
var previewPanel = document.querySelector('.preview-panel');
if (previewPanel !== null)
{
    var image, title, description, pharmacy, getlocation, submitButton, closeButton;

    image = previewPanel.querySelector('.preview-img');
    title = previewPanel.querySelector('*[data-target="title"]');
    description = previewPanel.querySelector('.description').firstElementChild;
    pharmacy = previewPanel.querySelector('*[data-target="pharmacy"]');
    getlocation = previewPanel.querySelector('*[data-target="preview-location"]');
    submitButton = previewPanel.querySelector('.preview-submit');
    closeButton = previewPanel.querySelector('.panel-close-button');
    closeButton2 = previewPanel.querySelector('.close-btn');

    var previewController = {
        show : function(object)
        {
            image.src = $url + object.image;
            title.innerText = object.title;
            description.innerText = object.description;
            pharmacy.innerText = object.pharmacy;
            getlocation.innerText = object.location;
            submitButton.href = $url + 'drugs/' + object.type + '/' + object.title;

            previewPanel.style.display = 'flex';

            setTimeout(function(){
                previewPanel.style.opacity = '1';
            },200);
        },
        close : function()
        {
            previewPanel.style.opacity = '0';

            setTimeout(function(){
                previewPanel.style.display = 'none';
            },500);
        }
    };


    // listen for preview clicks
    var previewRows = document.querySelectorAll('*[data-target="preview-panel"]');
    if (previewRows.length > 0)
    {
        [].forEach.call(previewRows, function(row){
            row.addEventListener('click', function(e){
            // find json
            var json = row.querySelector('td[data-json]');
            json = json.getAttribute('data-json');
            json = json.replace(/[¶]/g, '"');
            json = JSON.parse(json);
            if (typeof json == 'object')
            {
                previewController.show(json);
            }
            });
        });

        // close button
        closeButton.addEventListener('click', previewController.close);
        closeButton2.addEventListener('click', previewController.close);
    }
}