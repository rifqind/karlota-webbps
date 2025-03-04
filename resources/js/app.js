import '../css/app.css';
import './bootstrap';
import "@fontsource/poppins/400.css";
import "@fontsource/poppins/500.css";
import "@fontsource/poppins/600.css";
import "@fontsource/poppins/700.css";

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import VueTailwindDatepicker from "vue-tailwind-datepicker";
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import { library, config } from "@fortawesome/fontawesome-svg-core";
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import { faSearchengin } from "@fortawesome/free-brands-svg-icons";
import {
    faAngleLeft,
    faAngleUp,
    faAngleDown,
    faBan,
    faBars,
    faBarsStaggered,
    faMoneyBillTrendUp,
    faBookBookmark,
    faBuilding,
    faCheck,
    faChartPie,
    faChartLine,
    faCheckDouble,
    faChevronLeft,
    faChevronUp,
    faChevronDown,
    faCircle,
    faCircleChevronLeft,
    faCircleDown,
    faCircleXmark,
    faComputer,
    faCopy,
    faDiceD6,
    faDisplay,
    faEye,
    faFile,
    faFlagCheckered,
    faGraduationCap,
    faHome,
    faHourglassHalf,
    faKey,
    faList,
    faListOl,
    faLock,
    faMagnifyingGlass,
    faPaperPlane,
    faPen,
    faPenNib,
    faPencil,
    faPlus,
    faPlusCircle,
    faRecycle,
    faRightToBracket,
    faRotateLeft,
    faRotateRight,
    faSave,
    faScrewdriverWrench,
    faSignOutAlt,
    faSort,
    faSquarePen,
    faStar,
    faSunPlantWilt,
    faTable,
    faTachometerAlt,
    faTags,
    faThList,
    faTrashCan,
    faUser,
    faUserTie,
    faUsers,
} from "@fortawesome/free-solid-svg-icons";
library.add(
    faAngleLeft,
    faAngleUp,
    faAngleDown,
    faBan,
    faBars,
    faBarsStaggered,
    faMoneyBillTrendUp,
    faBookBookmark,
    faBuilding,
    faCheck,
    faChartPie,
    faChartLine,
    faCheckDouble,
    faChevronLeft,
    faChevronUp,
    faChevronDown,
    faCircle,
    faCircleChevronLeft,
    faCircleDown,
    faCircleXmark,
    faComputer,
    faCopy,
    faDiceD6,
    faDisplay,
    faEye,
    faFile,
    faFlagCheckered,
    faGraduationCap,
    faHome,
    faHourglassHalf,
    faKey,
    faList,
    faListOl,
    faLock,
    faMagnifyingGlass,
    faPaperPlane,
    faPen,
    faPenNib,
    faPencil,
    faPlus,
    faPlusCircle,
    faRecycle,
    faRightToBracket,
    faRotateLeft,
    faRotateRight,
    faSave,
    faScrewdriverWrench,
    faSignOutAlt,
    faSort,
    faSquarePen,
    faStar,
    faSunPlantWilt,
    faSearchengin,
    faTable,
    faTachometerAlt,
    faTags,
    faThList,
    faTrashCan,
    faUser,
    faUserTie,
    faUsers
);
config.autoAddCss = false;
const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        const vueApp = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .use(VueTailwindDatepicker)
            .component("font-awesome-icon", FontAwesomeIcon)
        return vueApp.mount(el)
    },
    progress: {
        color: "#8B1E3F",
        showSpinner: true,
        delay: 250,
    },
});
