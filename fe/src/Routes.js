import Vue from 'vue';
import Router from 'vue-router';

import Layout from '@/components/Layout/Layout';
import Login from '@/pages/Login/Login';
import Privacy from '@/pages/Login/Privacy';
import LoginCallback from '@/pages/Login/LoginCallback';
import Register from '@/pages/Login/Register';
import ForgotPassword from '@/pages/Login/ForgotPassword';
import ResetPassword from '@/pages/Login/ResetPassword';
import ErrorPage from '@/pages/Error/Error';
import UserAccount from '@/pages/UserAccount/UserAccount';
import Users from '@/pages/UserAccount/Users';
import Consultation from '@/pages/Consultation/Consultation';
import Patients from '@/pages/Patients/Patients';
import Drugs from '@/pages/Drugs/Drugs';
import Prescriptions from '@/pages/Prescriptions/Prescriptions';
import PrescriptionPage from '@/pages/Prescriptions/PrescriptionPage';
import DrugDetails from '@/pages/Drugs/DrugDetails';
import PatientDetails from '@/pages/Patients/PatientDetails';
import MyPayments from '@/pages/Payments/MyPayments';
import Payouts from '@/pages/Payments/Payouts';
import PaymentStatus from '@/pages/Payments/PaymentStatus';
import Directory from '@/pages/General/Directory';
import AddDirectory from '@/pages/General/AddDirectory';
import Feedback from '@/pages/General/Feedback';
import MonthlyCondition from '@/pages/General/MonthlyCondition';
import Reports from '@/pages/Reports/Reports';
import Sales from '@/pages/Sales/Sales';
import WalkInPatients from '@/pages/Patients/WalkInPatients';
import WalkInPatientDetails from '@/pages/Patients/WalkInPatientDetails';
import WalkInPatientPage from '@/pages/Patients/WalkInPatientPage';
import DrugPage from '@/pages/Drugs/DrugPage';
import UserDetailsPage from '@/pages/UserAccount/UserDetailsPage';
import PaymentReport from "@/pages/Reports/PaymentReport";
import PayoutReport from "@/pages/Reports/PayoutReport";
import ConsultationReport from "@/pages/Reports/ConsultationReport";


// Core
import TypographyPage from '@/pages/Typography/Typography';

// Tables
import TablesBasicPage from '@/pages/Tables/Basic';

// Maps
import GoogleMapPage from '@/pages/Maps/Google';

// Main
import AnalyticsPage from '@/pages/Dashboard/Dashboard';

// Charts
import ChartsPage from '@/pages/Charts/Charts';

// Ui
import IconsPage from '@/pages/Icons/Icons';
import NotificationsPage from '@/pages/Notifications/Notifications';


Vue.use(Router);

export default new Router({
  routes: [
    {
      path: '/privacy',
      name: 'Privacy',
      component: Privacy,
    },
    {
      path: '/login',
      name: 'Login',
      component: Login,
    },
    {
      path: '/login/callback',
      name: 'Login',
      component: LoginCallback,
    },
    {
      path: '/reset-password/:token',
      name: 'Reset',
      component: ResetPassword,
    },
    {
      path: '/register',
      name: 'Register',
      component: Register,
    },
    {
      path: '/forgot-password',
      name: 'ForgotPassword',
      component: ForgotPassword,
    },
    {
      path: '/error',
      name: 'Error',
      component: ErrorPage,
    },
    {
      path: '/app',
      name: 'Layout',
      component: Layout,
      children: [
        {
          path: 'dashboard',
          name: 'AnalyticsPage',
          component: AnalyticsPage,
        },
        {
          path: 'typography',
          name: 'TypographyPage',
          component: TypographyPage,
        },
        {
          path: 'components/icons',
          name: 'IconsPage',
          component: IconsPage,
        },
        {
          path: 'notifications',
          name: 'NotificationsPage',
          component: NotificationsPage,
        },
        {
          path: 'components/charts',
          name: 'ChartsPage',
          component: ChartsPage,
        },
        {
          path: 'tables',
          name: 'TablesBasicPage',
          component: TablesBasicPage,
        },
        {
          path: 'components/maps',
          name: 'GoogleMapPage',
          component: GoogleMapPage,
        },
        {
          path: 'account',
          name: 'account',
          component: UserAccount,
        },
        {
          path: 'users',
          name: 'users',
          component: Users,
        },
        {
          path: 'consultation',
          name: 'consultation',
          component: Consultation,
        },
        {
          path: 'patients',
          name: 'patients',
          component: Patients,
        },
        {
          path: 'patient-details/:patient',
          name: 'patientdetails',
          component: PatientDetails,
        },
        {
          path: 'sales',
          name: 'sales',
          component: Sales,
        },
        {
          path: 'walkinpatients',
          name: 'walkinpatients',
          component: WalkInPatients,
        },
        {
          path: 'walk-in-patient-details',
          name: 'walkinpatientdetails',
          component: WalkInPatientDetails,
        },
        {
          path: 'walk-in-patient/:patient',
          name: 'walkinpatientpage',
          component: WalkInPatientPage,
        },
        {
          path: 'drug-details',
          name: 'drugdetails',
          component: DrugDetails,
        },
        {
          path: 'drug/:drug',
          name: 'drugpage',
          component: DrugPage,
        },
        {
          path: 'drugs',
          name: 'drugs',
          component: Drugs,
        },
        {
          path: 'prescription/:prescription',
          name: 'prescriptionpage',
          component: PrescriptionPage,
        },
        {
          path: 'prescriptions',
          name: 'prescriptions',
          component: Prescriptions,
        },
        {
          path: 'reports',
          name: 'reports',
          component: Reports,
        },
        {
          path: 'user-details/:user',
          name: 'userdetailspage',
          component: UserDetailsPage,
        },
        {
          path: 'my-payments',
          name: 'mypayments',
          component: MyPayments,
        },
        {
          path: 'payouts',
          name: 'payouts',
          component: Payouts,
        },
        {
          path: 'payment-status',
          name: 'paymentstatus',
          component: PaymentStatus,
        },
        {
          path: 'directory',
          name: 'directory',
          component: Directory,
        },
        {
          path: 'feedback',
          name: 'feedback',
          component: Feedback,
        },
        {
          path: 'condition',
          name: 'condition',
          component: MonthlyCondition,
        },
        {
          path: 'AddDirectory',
          name: 'AddDirectory',
          component: AddDirectory,
        },
        {
          path: 'reports/PaymentReport',
          name: 'PaymentReport',
          component: PaymentReport,
        },
        {
          path: 'reports/ConsultationReport',
          name: 'ConsultationReport',
          component: ConsultationReport,
        },
        {
          path: 'reports/PayoutReport',
          name: 'PayoutReport',
          component: PayoutReport,
        },
      ],
    },
  ],
});