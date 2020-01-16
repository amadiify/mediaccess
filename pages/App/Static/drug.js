function previewClass() 
{
    var doc = document;
    var previewImage, previewImageWrapper, previewBtn;

    previewImage = doc.querySelector('.preview-image');
    previewImageWrapper = doc.querySelector('#preview-image-wrapper');
    previewBtn = doc.querySelector('.preview-btn');

    if (previewImageWrapper !== null)
    {   
        this.showPreview = function()
        {
            previewImageWrapper.style.display = 'flex';
            previewImage.style.display = 'flex';

            setTimeout(function(){
                previewImageWrapper.classList.add('show');
                setTimeout(function(){
                    previewImage.classList.add('show');
                },100);
            }, 100);
        }

        this.hidePreview = function()
        {
            previewImageWrapper.classList.remove('show');
            previewImage.classList.remove('show');

            setTimeout(function(){
                previewImageWrapper.removeAttribute('style');
                previewImage.removeAttribute('style');
            },600);
        }
    }

    this.followMouse = function(ev)
    {
        var x = ev.clientX, y = ev.clientY;
        var btn = ev.target.firstElementChild;

        if (btn !== null)
        {
            // btn.style.left = (x - 10) + 'px';
            // btn.style.top = (y - 10) + 'px';
        }
    };

    return this;
}

var previewClass = new previewClass;

function previewScreen(event)
{   
   previewClass.showPreview();
}

function closePreview(event)
{
    var target = event.target, hidepreview = false;

    var id, clss;
    id = target.getAttribute('id');
    clss = target.getAttribute('class');

    if (id == 'preview-image-wrapper')
    {
        hidepreview = true;
    }

    if (clss == 'btn btn-default')
    {
        hidepreview = true;
    }

    if (hidepreview)
    {
        previewClass.hidePreview();
    }
}

function followMouse(event)
{
    previewClass.followMouse(event);
}

function previewPrescribtionCode() 
{
    var doc = document;
    var modalWrapper;

    enterCodeModal = doc.querySelector('.enter-code-modal');
    modalWrapper = doc.querySelector('.modal-wrapper');

    if (enterCodeModal !== null)
    {   
        this.showPreview = function()
        {
            enterCodeModal.style.display = 'flex';
            modalWrapper.style.display = 'flex';

            setTimeout(function(){
                enterCodeModal.classList.add('show');
                setTimeout(function(){
                    modalWrapper.classList.add('show');
                },100);
            }, 100);
        }

        this.hidePreview = function()
        {
            enterCodeModal.classList.remove('show');
            modalWrapper.classList.remove('show');

            setTimeout(function(){
                enterCodeModal.removeAttribute('style');
                modalWrapper.removeAttribute('style');
            },600);
        }
    }

    return this;
}

function addToCart(event)
{
    event.preventDefault();
    var form = event.target.parentNode.parentNode.parentNode;
    // prescribed
    var prescribed = form.querySelector('*[name="prescribed"]');
    var modal = new previewPrescribtionCode();
    var close = enterCodeModal.querySelector('.close-modal');
    
    if (prescribed.value == 1)
    {
        modal.showPreview();

        close.addEventListener('click', modal.hidePreview);

        enterCodeModal.addEventListener('click', function(e){
            if (e.target.className == enterCodeModal.className)
            {
                modal.hidePreview();
            }
        });

        enterCodeModal.querySelector('.btn').addEventListener('click', function(e){
            // get value
            var input = enterCodeModal.querySelector('input');
            if (input.value.length > 3)
            {
                prescribed.value = input.value;
                form.submit();
            }
        });

        return false;
    }

    form.submit();
}