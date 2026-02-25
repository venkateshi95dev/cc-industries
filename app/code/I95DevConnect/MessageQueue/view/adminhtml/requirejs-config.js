/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */
let config = {
    map: {
        '*': {
            massagequeuegrid: 'I95DevConnect_MessageQueue/js/massagequeuegrid'
        }
    },
    "shim": {
        "massagequeuegrid":["jquery"]
    },
    deps: ["massagequeuegrid"]
};
