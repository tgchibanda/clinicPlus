// The Vue build version to load with the `import` command
// (runtime-only or standalone) has been set in webpack.base.conf with an alias.
import Vue from 'vue';
import BootstrapVue from 'bootstrap-vue';
import * as VueGoogleMaps from 'vue2-google-maps';
import VueTouch from 'vue-touch';
import Trend from 'vuetrend';
import Toasted from 'vue-toasted';
import VueApexCharts from 'vue-apexcharts';
import VueSweetalert2 from 'vue-sweetalert2';
import Vue2Filters from 'vue2-filters'
import axios from 'axios';
import VueAxios from 'vue-axios';
import { Form, HasError, AlertError } from 'vform';

// If you don't need the styles, do not connect
import 'sweetalert2/dist/sweetalert2.min.css';

import store from './store';
import router from './Routes';
import App from './App';
import { MonthPicker } from 'vue-month-picker'
import { MonthPickerInput } from 'vue-month-picker'
import layoutMixin from './mixins/layout';
import Widget from './components/Widget/Widget';
import VueElementLoading from 'vue-element-loading'
import UserDetails from "./components/UserDetails/UserDetails";
import UserRole from "./components/UserDetails/UserRole";
import MyPractice from "./components/UserDetails/MyPractice";
import BookConsultation from "./components/Patients/BookConsultation";
import PatientsTable from "./components/Patients/PatientsTable";
import ConsultationTable from "./components/Patients/ConsultationTable";
import PatientDetails from "./components/Patients/PatientDetails";
import PayPal from "./components/Payments/PayPal";
import Invoice from "./components/Payments/Invoice";
import PaymentsTable from "./components/Payments/PaymentsTable";
import DoctorsNotes from "./components/Consultation/DoctorsNotes";
import LocationPicker from "./components/General/LocationPicker";
import excel from 'vue-excel-export'
import VueSocialauth from 'vue-social-auth'
import VueAnalytics from 'vue-analytics'
// import './validationConfig';

Vue.use(BootstrapVue);
Vue.use(VueTouch);
Vue.use(Trend);
Vue.component('Widget', Widget);
Vue.use(VueGoogleMaps, {
  load: {
    key: 'AIzaSyB7OXmzfQYua_1LEhRdqsoYzyJOPh9hGLg',
  },
});
Vue.component('apexchart', VueApexCharts);
Vue.component('VueElementLoading', VueElementLoading);
Vue.component('user-details', UserDetails);
Vue.component('my-practice', MyPractice);
Vue.component('book-consultation', BookConsultation);
Vue.component('consultation-table', ConsultationTable);
Vue.component('patients-table', PatientsTable);
Vue.component('patient-details', PatientDetails);
Vue.component('user-role', UserRole);
Vue.component('paypal', PayPal);
Vue.component('invoice', Invoice);
Vue.component('payments-table', PaymentsTable);
Vue.component('doctors-notes', DoctorsNotes);
Vue.component('location-picker', LocationPicker);
Vue.component(HasError.name, HasError)
Vue.component(AlertError.name, AlertError)

Vue.mixin(layoutMixin);
Vue.use(Toasted, { duration: 10000 });
Vue.use(VueSweetalert2);
Vue.use(VueAxios, axios);
Vue.use(Vue2Filters);
Vue.use(MonthPicker)
Vue.use(MonthPickerInput)
Vue.use(require('vue-moment'));
Vue.use(excel);
Vue.use(VueSocialauth, {
  providers: {
    github: {
      clientId: '',
      redirectUri: 'http://localhost:3000/auth/github/callback' // Your client app URL
    }
  }
})

// global variable
//Vue.prototype.$base_url = 'https://apiv1.clinicPluszimbabwe.com/api/';
Vue.prototype.$base_url = 'http://localhost:8000/api/';
Vue.prototype.$axios = axios
window.Form = Form;
window.Fire = new Vue();

Vue.config.productionTip = false;
Vue.use(VueAnalytics, {
  id: 'G-RKBQTDN857',
  router
})

/* eslint-disable no-new */
new Vue({
  el: '#app',
  store,
  router,
  render: h => h(App),
});
