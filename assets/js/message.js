import {disableButtonOnSubmit} from './submit_button_disable';
import {initPlainTextFields} from './contact/plain_text';
import {initContactSendState} from './contact/send_state';
import {initMessagePhotos} from './contact/photos';

disableButtonOnSubmit();

initMessagePhotos();
initPlainTextFields();
initContactSendState();
