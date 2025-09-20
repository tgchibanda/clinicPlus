<template>
  <section>
    <h1 class="page-title">
      Reports — <span class="fw-semi-bold">Dashboard</span>
    </h1>

    <b-tabs content-class="mt-3">
      <!-- =======================
           TAB 1: DASHBOARD
      ======================== -->
      <b-tab title="Dashboard" active>
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h4 class="mb-0"><i class="fas fa-chart-bar mr-2"></i>Reports Dashboard</h4>
          <div class="text-muted small">
            <i class="fas fa-calendar mr-1"></i> Generated on {{ nowFmt }}
          </div>
        </div>

        <!-- Quick Stats -->
        <b-row>
          <b-col md="3" class="mb-3">
            <b-card bg-variant="primary" text-variant="white">
              <div class="d-flex justify-content-between">
                <div>
                  <h6 class="mb-1">Total Patients</h6>
                  <h3 class="mb-0">{{ safeNum(dashboard.patients_count) }}</h3>
                  <small>This month: {{ safeNum(dashboard.patients_this_month) }}</small>
                </div>
                <div class="align-self-center">
                  <i class="fas fa-users fa-2x"></i>
                </div>
              </div>
            </b-card>
          </b-col>
          <b-col md="3" class="mb-3">
            <b-card bg-variant="success" text-variant="white">
              <div class="d-flex justify-content-between">
                <div>
                  <h6 class="mb-1">Total Sales</h6>
                  <h3 class="mb-0">{{ money(safeNum(dashboard.sales_total)) }}</h3>
                  <small>This month: {{ money(safeNum(dashboard.sales_this_month)) }}</small>
                </div>
                <div class="align-self-center">
                  <i class="fas fa-dollar-sign fa-2x"></i>
                </div>
              </div>
            </b-card>
          </b-col>
          <b-col md="3" class="mb-3">
            <b-card bg-variant="warning" text-variant="white">
              <div class="d-flex justify-content-between">
                <div>
                  <h6 class="mb-1">Low Stock Items</h6>
                  <h3 class="mb-0">{{ safeNum(dashboard.low_stock_count) }}</h3>
                  <small>Requires attention</small>
                </div>
                <div class="align-self-center">
                  <i class="fas fa-exclamation-triangle fa-2x"></i>
                </div>
              </div>
            </b-card>
          </b-col>
          <b-col md="3" class="mb-3">
            <b-card bg-variant="info" text-variant="white">
              <div class="d-flex justify-content-between">
                <div>
                  <h6 class="mb-1">Active Prescriptions</h6>
                  <h3 class="mb-0">{{ safeNum(dashboard.active_prescriptions_count) }}</h3>
                  <small>Pending & Partial</small>
                </div>
                <div class="align-self-center">
                  <i class="fas fa-prescription-bottle-alt fa-2x"></i>
                </div>
              </div>
            </b-card>
          </b-col>
        </b-row>

        <!-- Two report cards -->
        <b-row>
          <!-- Stock Reports -->
          <b-col md="6" class="mb-4">
            <b-card class="h-100">
              <template #header>
                <h5 class="mb-0"><i class="fas fa-pills mr-2"></i>Stock Reports</h5>
              </template>
              <p class="mb-3">Monitor inventory levels, low stock alerts, and expired medications.</p>
              <b-row class="text-center mb-3">
                <b-col cols="4">
                  <div class="text-danger">
                    <i class="fas fa-exclamation-circle fa-2x"></i>
                    <div class="mt-2">
                      <strong>{{ safeNum(dashboard.low_stock_count) }}</strong>
                      <br><small>Low Stock</small>
                    </div>
                  </div>
                </b-col>
                <b-col cols="4">
                  <div class="text-warning">
                    <i class="fas fa-calendar-times fa-2x"></i>
                    <div class="mt-2">
                      <strong>{{ safeNum(dashboard.expiring_soon_count) }}</strong>
                      <br><small>Expiring Soon</small>
                    </div>
                  </div>
                </b-col>
                <b-col cols="4">
                  <div class="text-dark">
                    <i class="fas fa-ban fa-2x"></i>
                    <div class="mt-2">
                      <strong>{{ safeNum(dashboard.expired_count) }}</strong>
                      <br><small>Expired</small>
                    </div>
                  </div>
                </b-col>
              </b-row>

              <b-button variant="primary" block @click="activeTab='Stock'">
                <i class="fas fa-eye mr-1"></i> View Detailed Stock Report
              </b-button>
            </b-card>
          </b-col>

          <!-- Sales Reports -->
          <b-col md="6" class="mb-4">
            <b-card class="h-100">
              <template #header>
                <h5 class="mb-0"><i class="fas fa-chart-line mr-2"></i>Sales Reports</h5>
              </template>
              <p class="mb-3">Analyze sales performance, revenue trends, and top-selling medications.</p>
              <b-row class="text-center mb-3">
                <b-col cols="6">
                  <div class="text-success">
                    <i class="fas fa-dollar-sign fa-2x"></i>
                    <div class="mt-2">
                      <strong>{{ money(safeNum(dashboard.todays_sales_total)) }}</strong>
                      <br><small>Today's Sales</small>
                    </div>
                  </div>
                </b-col>
                <b-col cols="6">
                  <div class="text-info">
                    <i class="fas fa-receipt fa-2x"></i>
                    <div class="mt-2">
                      <strong>{{ safeNum(dashboard.todays_sales_count) }}</strong>
                      <br><small>Transactions</small>
                    </div>
                  </div>
                </b-col>
              </b-row>

              <b-button variant="success" block @click="activeTab='Sales'">
                <i class="fas fa-eye mr-1"></i> View Detailed Sales Report
              </b-button>
            </b-card>
          </b-col>

          <!-- Doctor Performance -->
          <b-col md="6" class="mb-4">
            <b-card class="h-100">
              <template #header>
                <h5 class="mb-0"><i class="fas fa-user-md mr-2"></i>Doctor Performance</h5>
              </template>
              <p class="mb-3">Track doctor consultations, prescriptions issued, and patient load.</p>
              <b-row class="text-center mb-3">
                <b-col cols="6">
                  <div class="text-primary">
                    <i class="fas fa-stethoscope fa-2x"></i>
                    <div class="mt-2">
                      <strong>{{ safeNum(dashboard.todays_patients_count) }}</strong>
                      <br><small>Today's Patients</small>
                    </div>
                  </div>
                </b-col>
                <b-col cols="6">
                  <div class="text-secondary">
                    <i class="fas fa-prescription fa-2x"></i>
                    <div class="mt-2">
                      <strong>{{ safeNum(dashboard.todays_prescriptions_count) }}</strong>
                      <br><small>Prescriptions</small>
                    </div>
                  </div>
                </b-col>
              </b-row>
              <b-alert show variant="info" class="mb-0">
                <small><i class="fas fa-info-circle mr-1"></i> Detailed doctor reports coming soon</small>
              </b-alert>
            </b-card>
          </b-col>

          <!-- Analytics -->
          <b-col md="6" class="mb-4">
            <b-card class="h-100">
              <template #header>
                <h5 class="mb-0"><i class="fas fa-chart-pie mr-2"></i>Analytics Overview</h5>
              </template>
              <p class="mb-3">General clinic statistics and performance metrics.</p>
              <b-row class="text-center mb-3">
                <b-col cols="6">
                  <div class="text-dark">
                    <i class="fas fa-user-md fa-2x"></i>
                    <div class="mt-2">
                      <strong>{{ safeNum(dashboard.active_doctors_count) }}</strong>
                      <br><small>Active Doctors</small>
                    </div>
                  </div>
                </b-col>
                <b-col cols="6">
                  <div class="text-success">
                    <i class="fas fa-pills fa-2x"></i>
                    <div class="mt-2">
                      <strong>{{ safeNum(dashboard.drugs_count) }}</strong>
                      <br><small>Available Drugs</small>
                    </div>
                  </div>
                </b-col>
              </b-row>
              <b-alert show variant="secondary" class="mb-0">
                <small><i class="fas fa-info-circle mr-1"></i> Advanced analytics coming soon</small>
              </b-alert>
            </b-card>
          </b-col>
        </b-row>

        <!-- Recent Activity -->
        <b-card>
          <template #header>
            <h5 class="mb-0"><i class="fas fa-clock mr-2"></i>Recent Activity (Today)</h5>
          </template>

          <b-row>
            <b-col md="6" class="mb-3 mb-md-0">
              <h6 class="mb-3">Latest Patients</h6>
              <div class="list-group list-group-flush">
                <div
                  v-for="p in (dashboard.recent_patients || [])"
                  :key="'rp-'+p.id"
                  class="list-group-item d-flex justify-content-between"
                >
                  <div>
                    <strong>{{ p.full_name || (p.first_name + ' ' + p.last_name) }}</strong>
                    <br>
                    <small class="text-muted">
                      Dr. {{ (p.doctor && p.doctor.name) || 'Not Assigned' }}
                      <span v-if="p.visit_time"> — {{ p.visit_time }}</span>
                    </small>
                  </div>
                  <span class="badge" :class="p.status === 'completed' ? 'bg-success' : 'bg-warning'">
                    {{ capFirst(p.status || 'pending') }}
                  </span>
                </div>
                <div v-if="!(dashboard.recent_patients || []).length" class="list-group-item text-muted text-center">
                  <i class="fas fa-info-circle"></i> No patients registered today
                </div>
              </div>
            </b-col>

            <b-col md="6">
              <h6 class="mb-3">Recent Sales</h6>
              <div class="list-group list-group-flush">
                <div
                  v-for="s in (dashboard.recent_sales || [])"
                  :key="'rs-'+s.id"
                  class="list-group-item d-flex justify-content-between"
                >
                  <div>
                    <strong>{{ (s.patient && s.patient.full_name) || '—' }}</strong>
                    <br>
                    <small class="text-muted">
                      {{ capFirst(s.payment_method || 'cash') }}
                      <span v-if="s.created_time"> — {{ s.created_time }}</span>
                    </small>
                  </div>
                  <span class="badge bg-success">
                    {{ money(s.total_amount || 0) }}
                  </span>
                </div>
                <div v-if="!(dashboard.recent_sales || []).length" class="list-group-item text-muted text-center">
                  <i class="fas fa-info-circle"></i> No sales recorded today
                </div>
              </div>
            </b-col>
          </b-row>
        </b-card>
      </b-tab>

      <!-- =======================
           TAB 2: STOCK REPORT
      ======================== -->
      <b-tab title="Stock" @click="ensureStockLoaded">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h4 class="mb-0"><i class="fas fa-pills mr-2"></i>Pharmacy Stock Report</h4>
          <div class="btn-group">
            <b-button variant="outline-primary" @click="printPage">
              <i class="fas fa-print mr-1"></i> Print Report
            </b-button>
            <b-button variant="outline-success" @click="exportStockCSV">
              <i class="fas fa-file-csv mr-1"></i> Export CSV
            </b-button>
          </div>
        </div>

        <!-- Alerts -->
        <b-row class="mb-3">
          <b-col md="4" class="mb-3 mb-md-0">
            <b-alert show variant="danger">
              <h6 class="mb-1"><i class="fas fa-exclamation-triangle mr-1"></i>Critical Low Stock</h6>
              <p class="mb-0">{{ safeNum(stock.critical_low_count) }} items need immediate restocking</p>
            </b-alert>
          </b-col>
          <b-col md="4" class="mb-3 mb-md-0">
            <b-alert show variant="warning">
              <h6 class="mb-1"><i class="fas fa-exclamation-circle mr-1"></i>Low Stock Alert</h6>
              <p class="mb-0">{{ safeNum(stock.low_stock_count) }} items below minimum level</p>
            </b-alert>
          </b-col>
          <b-col md="4">
            <b-alert show variant="info">
              <h6 class="mb-1"><i class="fas fa-calendar-times mr-1"></i>Expiring Soon</h6>
              <p class="mb-0">{{ safeNum(stock.expiring_soon_count) }} items expire within 3 months</p>
            </b-alert>
          </b-col>
        </b-row>

        <!-- Overview Cards -->
        <b-row class="mb-3">
          <b-col md="3" class="mb-3">
            <b-card class="text-center">
              <h5 class="text-success">Total Stock Value</h5>
              <h2 class="text-success">{{ money(safeNum(stock.total_stock_value)) }}</h2>
            </b-card>
          </b-col>
          <b-col md="3" class="mb-3">
            <b-card class="text-center">
              <h5 class="text-info">Total Items</h5>
              <h2 class="text-info">{{ safeNum(stock.total_drugs) }}</h2>
              <small class="text-muted">Different medications</small>
            </b-card>
          </b-col>
          <b-col md="3" class="mb-3">
            <b-card class="text-center">
              <h5 class="text-warning">Categories</h5>
              <h2 class="text-warning">{{ safeNum(stock.categories_count) }}</h2>
              <small class="text-muted">Drug categories</small>
            </b-card>
          </b-col>
          <b-col md="3" class="mb-3">
            <b-card class="text-center">
              <h5 class="text-danger">Expired</h5>
              <h2 class="text-danger">{{ safeNum(stock.expired_count) }}</h2>
              <small class="text-muted">Need removal</small>
            </b-card>
          </b-col>
        </b-row>

        <!-- Stock table -->
        <b-card>
          <template #header>
            <h5 class="mb-0"><i class="fas fa-table mr-2"></i>Stock Details</h5>
          </template>

          <div class="table-responsive">
            <table class="table table-striped table-hover" ref="stockTable">
              <thead class="thead-dark">
                <tr>
                  <th>Drug Name</th>
                  <th>Category</th>
                  <th>Current Stock</th>
                  <th>Minimum Level</th>
                  <th>Unit Price</th>
                  <th>Total Value</th>
                  <th>Expiry Date</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="d in stock.drugs"
                  :key="d.id"
                  :class="rowClass(d)"
                >
                  <td>
                    <strong>{{ d.name }}</strong>
                    <br v-if="d.description"><small class="text-muted" v-if="d.description">{{ d.description }}</small>
                  </td>
                  <td><span class="badge bg-secondary">{{ d.category || 'N/A' }}</span></td>
                  <td>
                    <span
                      class="badge fs-6"
                      :class="d.stock_quantity <= 5 ? 'bg-danger' : (isLowStock(d) ? 'bg-warning' : 'bg-success')"
                    >
                      {{ d.stock_quantity }} {{ d.unit }}
                    </span>
                  </td>
                  <td>{{ d.minimum_stock_level }}</td>
                  <td>{{ money(d.selling_price) }}</td>
                  <td class="font-weight-bold">{{ money(d.stock_quantity * d.selling_price) }}</td>
                  <td>
                    <span v-if="d.expiry_date"
                          class="badge"
                          :class="expiryBadge(d)">
                      {{ formatDate(d.expiry_date) }}
                    </span>
                    <span v-else class="text-muted">Not set</span>
                  </td>
                  <td>
                    <span v-if="isExpired(d)" class="badge bg-danger">Expired</span>
                    <span v-else-if="d.stock_quantity <= 5" class="badge bg-danger">Critical</span>
                    <span v-else-if="isLowStock(d)" class="badge bg-warning">Low Stock</span>
                    <span v-else class="badge bg-success">Good</span>
                  </td>
                  <td>
                    <b-button size="sm" variant="outline-primary" class="mr-1" @click="goDrugShow(d.id)">
                      <i class="fas fa-eye"></i>
                    </b-button>
                    <b-button size="sm" variant="outline-secondary" @click="goDrugEdit(d.id)">
                      <i class="fas fa-edit"></i>
                    </b-button>
                  </td>
                </tr>

                <tr v-if="!stock.drugs.length">
                  <td colspan="9" class="text-center text-muted">No stock data.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </b-card>

        <!-- Simple analyses (progress bars) -->
        <b-row class="mt-3">
          <b-col md="6" class="mb-3">
            <b-card>
              <template #header><h5 class="mb-0"><i class="fas fa-chart-pie mr-2"></i>Stock by Category</h5></template>
              <div v-for="c in stock.by_category" :key="c.category" class="mb-3">
                <div class="d-flex justify-content-between mb-1">
                  <span class="font-weight-bold">{{ c.category || 'Uncategorized' }}</span>
                  <span class="badge bg-primary">{{ money(c.value) }}</span>
                </div>
                <div class="progress">
                  <div class="progress-bar" :style="{ width: pct(c.value, stock.total_stock_value) + '%' }"
                       :title="pct(c.value, stock.total_stock_value).toFixed(1) + '%'">
                  </div>
                </div>
                <small class="text-muted">{{ c.items }} items ({{ pct(c.value, stock.total_stock_value).toFixed(1) }}%)</small>
              </div>
              <div v-if="!stock.by_category.length" class="text-muted">No category breakdown.</div>
            </b-card>
          </b-col>

          <b-col md="6" class="mb-3">
            <b-card>
              <template #header><h5 class="mb-0"><i class="fas fa-chart-bar mr-2"></i>Stock Status Distribution</h5></template>

              <div v-for="row in stock.status_counts" :key="row.key" class="mb-3">
                <div class="d-flex justify-content-between mb-1">
                  <span class="font-weight-bold" :class="row.classText">{{ row.title }}</span>
                  <span class="badge" :class="row.classBadge">{{ row.count }} items</span>
                </div>
                <div class="progress">
                  <div class="progress-bar" :class="row.classBar" :style="{ width: pct(row.count, stock.total_drugs) + '%' }"></div>
                </div>
              </div>

              <div v-if="!stock.total_drugs" class="text-muted">No stock data.</div>
            </b-card>
          </b-col>
        </b-row>
      </b-tab>

      <!-- =======================
           TAB 3: SALES REPORT
      ======================== -->
      <b-tab title="Sales" @click="ensureSalesLoaded">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h4 class="mb-0"><i class="fas fa-chart-line mr-2"></i>Sales Report</h4>
          <div class="btn-group">
            <b-button variant="outline-primary" @click="printPage">
              <i class="fas fa-print mr-1"></i> Print Report
            </b-button>
            <b-button variant="outline-success" @click="exportSalesCSV">
              <i class="fas fa-file-csv mr-1"></i> Export CSV
            </b-button>
          </div>
        </div>

        <!-- Date Filter -->
        <b-card class="mb-3">
          <b-form @submit.prevent="loadSales">
            <b-row>
              <b-col md="4" class="mb-2">
                <b-form-group label="Start Date">
                  <b-form-input type="date" v-model="salesFilter.start_date" required />
                </b-form-group>
              </b-col>
              <b-col md="4" class="mb-2">
                <b-form-group label="End Date">
                  <b-form-input type="date" v-model="salesFilter.end_date" required />
                </b-form-group>
              </b-col>
              <b-col md="4" class="mb-2 d-flex align-items-end">
                <b-button type="submit" variant="primary" block>
                  <i class="fas fa-search mr-1"></i> Generate Report
                </b-button>
              </b-col>
            </b-row>
            <small class="text-muted">
              <i class="fas fa-info-circle mr-1"></i>
              Report period: {{ prettyDate(salesFilter.start_date) }} to {{ prettyDate(salesFilter.end_date) }}
            </small>
          </b-form>
        </b-card>

        <!-- Summary Cards -->
        <b-row class="mb-3">
          <b-col md="3" class="mb-3">
            <b-card bg-variant="success" text-variant="white">
              <div class="d-flex justify-content-between">
                <div>
                  <h6 class="mb-1">Total Revenue</h6>
                  <h3 class="mb-0">{{ money(safeNum(sales.total_revenue)) }}</h3>
                </div>
                <div class="align-self-center"><i class="fas fa-dollar-sign fa-2x"></i></div>
              </div>
              <small>{{ prettyDate(salesFilter.start_date) }} - {{ prettyDate(salesFilter.end_date) }}</small>
            </b-card>
          </b-col>
          <b-col md="3" class="mb-3">
            <b-card bg-variant="info" text-variant="white">
              <div class="d-flex justify-content-between">
                <div>
                  <h6 class="mb-1">Transactions</h6>
                  <h3 class="mb-0">{{ safeNum(sales.transactions_count) }}</h3>
                </div>
                <div class="align-self-center"><i class="fas fa-receipt fa-2x"></i></div>
              </div>
              <small>{{ prettyDate(salesFilter.start_date) }} - {{ prettyDate(salesFilter.end_date) }}</small>
            </b-card>
          </b-col>
          <b-col md="3" class="mb-3">
            <b-card>
              <h6 class="mb-1">Avg. Order Value</h6>
              <h3 class="mb-0">{{ money(avgOrderValue) }}</h3>
              <small class="text-muted">Total / Transactions</small>
            </b-card>
          </b-col>
          <b-col md="3" class="mb-3">
            <b-card>
              <h6 class="mb-1">Top Payment</h6>
              <h3 class="mb-0">{{ capFirst(sales.top_payment_method || '—') }}</h3>
              <small class="text-muted">Most used method</small>
            </b-card>
          </b-col>
        </b-row>

        <!-- Sales Table -->
        <b-card>
          <template #header><h5 class="mb-0"><i class="fas fa-table mr-2"></i>Sales</h5></template>
          <div class="table-responsive">
            <table class="table table-striped table-hover" ref="salesTable">
              <thead class="thead-dark">
                <tr>
                  <th>ID</th>
                  <th>Patient</th>
                  <th>Payment</th>
                  <th>Total</th>
                  <th>Date</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="s in sales.rows" :key="s.id">
                  <td>{{ s.id }}</td>
                  <td>{{ (s.patient && (s.patient.full_name || (s.patient.first_name + ' ' + s.patient.last_name))) || '—' }}</td>
                  <td>{{ capFirst(s.payment_method || 'cash') }}</td>
                  <td>{{ money(s.total_amount || 0) }}</td>
                  <td>{{ dateTime(s.created_at) }}</td>
                </tr>
                <tr v-if="!sales.rows.length">
                  <td colspan="5" class="text-center text-muted">No sales in this period.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </b-card>
      </b-tab>
    </b-tabs>

    <vue-element-loading
      :active="loading"
      :is-full-screen="true"
      :size="'80'"
      :color="'#FF6700'"
      :text="'Loading…'"
    />
  </section>
</template>

<script>
import authHeader from "@/services/auth-header";

export default {
  name: "Reports",
  data() {
    const today = new Date();
    const ymd = (d) =>
      `${d.getFullYear()}-${("0" + (d.getMonth() + 1)).slice(-2)}-${("0" + d.getDate()).slice(-2)}`;

    const start = new Date(today.getFullYear(), today.getMonth(), 1);

    return {
      activeTab: "Dashboard",

      loading: false,

      // Dashboard dataset
      dashboard: {
        // expected fields:
        // patients_count, patients_this_month, sales_total, sales_this_month, low_stock_count,
        // active_prescriptions_count, expiring_soon_count, expired_count,
        // todays_sales_total, todays_sales_count, todays_patients_count, todays_prescriptions_count,
        // active_doctors_count, drugs_count,
        // recent_patients: [], recent_sales: []
      },

      // Stock dataset
      stock: {
        drugs: [],
        critical_low_count: 0,
        low_stock_count: 0,
        expiring_soon_count: 0,
        total_stock_value: 0,
        total_drugs: 0,
        categories_count: 0,
        expired_count: 0,
        by_category: [], // [{ category, value, items }]
        status_counts: [] // [{ key, title, count, classText, classBadge, classBar }]
      },

      // Sales dataset
      sales: {
        rows: [],
        total_revenue: 0,
        transactions_count: 0,
        top_payment_method: null
      },

      salesFilter: {
        start_date: ymd(start),
        end_date: ymd(today)
      }
    };
  },
  computed: {
    nowFmt() {
      const d = new Date();
      return d.toLocaleString("en-AU", {
        day: "2-digit",
        month: "short",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit"
      });
    },
    avgOrderValue() {
      const total = Number(this.sales.total_revenue || 0);
      const n = Number(this.sales.transactions_count || 0);
      return n > 0 ? total / n : 0;
    }
  },
  methods: {
    // ===== API LOADERS =====
    loadDashboard() {
      this.loading = true;
      return this.$axios
        .get(this.$base_url + "reports", authHeader())
        .then(({ data }) => {
          this.dashboard = (data && data.data) || {};
        })
        .catch((e) => {
          const msg =
            (e.response && e.response.data && e.response.data.message) ||
            e.message ||
            e.toString();
          this.$swal("Error", msg, "error");
        })
        .finally(() => (this.loading = false));
    },

    ensureStockLoaded() {
      if (this.stock.drugs.length) return;
      this.loadStock();
    },

    loadStock() {
      this.loading = true;
      return this.$axios
        .get(this.$base_url + "reports/stock", authHeader())
        .then(({ data }) => {
          const payload = (data && data.data) || {};
          // Normalize
          this.stock.drugs = payload.drugs || [];
          this.stock.critical_low_count = payload.critical_low_count || 0;
          this.stock.low_stock_count = payload.low_stock_count || 0;
          this.stock.expiring_soon_count = payload.expiring_soon_count || 0;
          this.stock.total_stock_value = payload.total_stock_value || 0;
          this.stock.total_drugs = payload.total_drugs || this.stock.drugs.length;
          this.stock.categories_count = payload.categories_count || 0;
          this.stock.expired_count = payload.expired_count || 0;
          this.stock.by_category = payload.by_category || [];
          // Build status counts if not provided
          if (payload.status_counts) {
            this.stock.status_counts = payload.status_counts;
          } else {
            const good = this.stock.drugs.filter((d) => !this.isLowStock(d) && !this.isExpired(d)).length;
            const low = this.stock.drugs.filter((d) => this.isLowStock(d) && !this.isExpired(d)).length;
            const exp = this.stock.drugs.filter((d) => this.isExpired(d)).length;
            this.stock.status_counts = [
              { key: "good", title: "Good Stock", count: good, classText: "text-success", classBadge: "bg-success", classBar: "bg-success" },
              { key: "low", title: "Low Stock", count: low, classText: "text-warning", classBadge: "bg-warning", classBar: "bg-warning" },
              { key: "expired", title: "Expired", count: exp, classText: "text-danger", classBadge: "bg-danger", classBar: "bg-danger" }
            ];
          }
        })
        .catch((e) => {
          const msg =
            (e.response && e.response.data && e.response.data.message) ||
            e.message ||
            e.toString();
          this.$swal("Error", msg, "error");
        })
        .finally(() => (this.loading = false));
    },

    ensureSalesLoaded() {
      if (this.sales.rows.length) return;
      this.loadSales();
    },

    loadSales() {
      this.loading = true;
      const params = {
        start_date: this.salesFilter.start_date,
        end_date: this.salesFilter.end_date
      };
      return this.$axios
        .get(this.$base_url + "reports/sales", { ...authHeader(), params })
        .then(({ data }) => {
          const payload = (data && data.data) || {};
          this.sales.rows = payload.rows || payload.sales || [];
          this.sales.total_revenue = payload.total_revenue || payload.totalSales || 0;
          this.sales.transactions_count = payload.transactions_count || (this.sales.rows ? this.sales.rows.length : 0);
          this.sales.top_payment_method = payload.top_payment_method || null;
        })
        .catch((e) => {
          const msg =
            (e.response && e.response.data && e.response.data.message) ||
            e.message ||
            e.toString();
          this.$swal("Error", msg, "error");
        })
        .finally(() => (this.loading = false));
    },

    // ===== NAV HELPERS =====
    goDrugShow(id) {
      this.$router.push({ name: "drugdetailspage", params: { drug: id } });
    },
    goDrugEdit(id) {
      this.$router.push({ name: "drugeditpage", params: { drug: id } });
    },

    // ===== UI HELPERS =====
    money(v) {
      const n = Number(v || 0);
      return isNaN(n) ? "$0.00" : `$${n.toFixed(2)}`;
    },
    capFirst(v) {
      if (!v) return "";
      return v.charAt(0).toUpperCase() + v.slice(1);
    },
    safeNum(v) {
      const n = Number(v);
      return isNaN(n) ? 0 : n;
    },
    dateTime(val) {
      if (!val) return "—";
      const d = new Date(val);
      return isNaN(d.getTime())
        ? val
        : d.toLocaleString("en-AU", {
            day: "2-digit",
            month: "short",
            year: "numeric",
            hour: "2-digit",
            minute: "2-digit"
          });
    },
    formatDate(val) {
      if (!val) return "—";
      const d = new Date(val);
      return isNaN(d.getTime())
        ? val
        : d.toLocaleDateString("en-AU", {
            day: "2-digit",
            month: "short",
            year: "numeric"
          });
    },
    isExpired(d) {
      if (!d.expiry_date) return false;
      return new Date(d.expiry_date) < new Date();
    },
    isLowStock(d) {
      const min = Number(d.minimum_stock_level || 0);
      return Number(d.stock_quantity || 0) <= min;
    },
    expiryBadge(d) {
      if (!d.expiry_date) return "bg-secondary";
      const dt = new Date(d.expiry_date);
      const soon = new Date();
      soon.setMonth(soon.getMonth() + 3);
      if (dt < new Date()) return "bg-danger";
      if (dt < soon) return "bg-warning";
      return "bg-secondary";
    },
    rowClass(d) {
      return {
        "table-danger": this.isExpired(d),
        "table-warning": this.isLowStock(d) && !this.isExpired(d)
      };
    },
    pct(part, total) {
      part = Number(part || 0);
      total = Number(total || 0);
      if (total <= 0) return 0;
      return (part / total) * 100;
    },
    prettyDate(v) {
      if (!v) return "—";
      const d = new Date(v);
      return isNaN(d.getTime())
        ? v
        : d.toLocaleDateString("en-AU", { day: "2-digit", month: "short", year: "numeric" });
    },

    // ===== ACTIONS =====
    printPage() {
      window.print();
    },

    exportStockCSV() {
      // Build CSV from current stock table rows
      const table = this.$refs.stockTable;
      if (!table) return;

      const rows = table.querySelectorAll("tr");
      const csv = [];
      for (let i = 0; i < rows.length; i++) {
        const cols = rows[i].querySelectorAll("td, th");
        const row = [];
        // Exclude Actions column (last one)
        for (let j = 0; j < cols.length - 1; j++) {
          let cellText = cols[j].innerText.replace(/"/g, '""');
          cellText = cellText.replace(/\n/g, " ").trim();
          row.push('"' + cellText + '"');
        }
        csv.push(row.join(","));
      }
      this.downloadCSV(csv.join("\n"), `stock-report-${new Date().toISOString().slice(0,10)}.csv`);
    },

    exportSalesCSV() {
      const header = ["ID", "Patient", "Payment", "Total", "Date"].map((h) => `"${h}"`).join(",");
      const lines = (this.sales.rows || []).map((s) => {
        const patient =
          (s.patient && (s.patient.full_name || `${s.patient.first_name || ""} ${s.patient.last_name || ""}`.trim())) ||
          "";
        const cells = [
          s.id,
          patient,
          this.capFirst(s.payment_method || "cash"),
          Number(s.total_amount || 0).toFixed(2),
          this.dateTime(s.created_at)
        ].map((c) => `"${String(c).replace(/"/g, '""')}"`);
        return cells.join(",");
      });
      this.downloadCSV([header, ...lines].join("\n"), `sales-report-${new Date().toISOString().slice(0,10)}.csv`);
    },

    downloadCSV(content, filename) {
      const blob = new Blob([content], { type: "text/csv" });
      const url = URL.createObjectURL(blob);
      const a = document.createElement("a");
      a.href = url;
      a.download = filename;
      a.click();
      URL.revokeObjectURL(url);
    }
  },
  created() {
    this.loadDashboard();
  }
};
</script>

<style scoped>
.page-title { margin-bottom: 12px; }
.table td, .table th { vertical-align: middle; }
.fs-6 { font-size: 1rem; }

/* Print-friendly */
@media print {
  .btn, .btn-group, .nav, .pagination, .form-group, .input-group { display: none !important; }
  .card { box-shadow: none !important; border: 1px solid #ddd; }
  body { background: #fff !important; }
}
</style>
