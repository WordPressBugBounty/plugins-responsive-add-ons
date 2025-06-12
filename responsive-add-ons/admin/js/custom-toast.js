function displayToast(msg, status) {
    let background;
    let textColor = '#FFFFFF'; 
    switch (status) {
        case 'success':
            background = 'linear-gradient(90deg, #2BAD47, #76C893)'; 
            break;
        case 'error':
            background = 'linear-gradient(90deg, #FF5151, #FF7A7A)'; 
            break;
        case 'info':
        default:
            background = 'linear-gradient(90deg, #007BFF, #4DA8F0)'; 
            break;
    }

    Toastify({
        text: msg,
        duration: 3000,
        gravity: 'top',
        position: 'center',
        stopOnFocus: true,
        offset: {
            x: 0,
            y: 30,
        },
        style: {
            background,          
            color: textColor,    
            borderRadius: '8px', 
            boxShadow: '0 4px 6px rgba(0, 0, 0, 0.1)',
            padding: '10px 20px', 
            fontSize: '16px',    
            fontWeight: 'bold',  
            textAlign: 'center', 
        },
    }).showToast();
}

$(document).ready(function () {
    $('#test-toast').on('click', function () {
        displayToast('This is a beautifully styled toast message!', 'success');
    });
});
