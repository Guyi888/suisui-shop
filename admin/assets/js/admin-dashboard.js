(function(window, document, $) {
  'use strict';

  var text = {
    chartUnavailable: '\u56fe\u8868\u7ec4\u4ef6\u672a\u52a0\u8f7d',
    dashboardLoadFailed: '\u6570\u636e\u52a0\u8f7d\u5931\u8d25',
    orderSeries: '\u8ba2\u5355\u91cf',
    moneySeries: '\u4ea4\u6613\u989d',
    visitSeries: '\u8bbf\u95ee\u91cf',
    ipSeries: '\u72ec\u7acbIP',
    emptyVisit: '\u6682\u65e0\u8bbf\u95ee\u8bb0\u5f55',
    loadingVisit: '\u6b63\u5728\u52a0\u8f7d\u8bbf\u95ee\u8bb0\u5f55...',
    visitError: '\u7f51\u7edc\u9519\u8bef\uff0c\u65e0\u6cd5\u52a0\u8f7d\u8bbf\u95ee\u8bb0\u5f55',
    requestFailed: '\u52a0\u8f7d\u5931\u8d25\uff1a',
    firstPage: '\u9996\u9875',
    lastPage: '\u672b\u9875'
  };

  var dashboard = {
    currentPage: 1,
    pageSize: 20,
    tradeChartSeries: null,
    visitChartSeries: null,
    tradeChartTicks: null,
    visitChartTicks: null,
    resizeTimer: null,

    init: function() {
      if (!document.getElementById('chart-classic-dash')) {
        return;
      }

      this.cache();
      this.prepareVisitModal();
      this.bind();
      this.startServerClock();
      this.fetchDashboardData();
    },

    cache: function() {
      this.$tradeChart = $('#chart-classic-dash');
      this.$visitChart = $('#visit-chart');
      this.$visitSection = $('#visitChartSection');
      this.$visitRange = $('#visitChartRange');
      this.$serverTime = $('#serverTime');
      this.$visitTable = $('#visitDetailTable');
      this.$visitEmpty = $('#visitDetailEmpty');
      this.$visitLoading = $('#visitDetailLoading');
      this.$visitPagination = $('#visitDetailPagination');
      this.$visitModal = $('#visitDetailModal');
    },

    prepareVisitModal: function() {
      if (this.$visitModal.length) {
        this.$visitModal.appendTo(document.body);
      }
    },

    bind: function() {
      var self = this;

      $(document).on('click', '#viewVisitDetails', function() {
        self.currentPage = 1;
        self.loadVisitDetails(self.currentPage);
        self.$visitModal.modal('show');
      });

      this.$visitPagination.on('click', 'a[data-page]', function(event) {
        var page = parseInt($(this).data('page'), 10);

        event.preventDefault();

        if (!page || page === self.currentPage) {
          return;
        }

        self.currentPage = page;
        self.loadVisitDetails(self.currentPage);
      });

      this.$visitModal.on('hidden.bs.modal', function() {
        self.currentPage = 1;
        self.resetVisitModal();
      });

      $(window).on('resize.adminDashboard', function() {
        window.clearTimeout(self.resizeTimer);
        self.resizeTimer = window.setTimeout(function() {
          self.redrawCharts();
        }, 120);
      });
    },

    fetchDashboardData: function() {
      var self = this;

      $.ajax({
        type: 'GET',
        url: 'ajax.php?act=getcount',
        data: {
          _: Date.now()
        },
        cache: false,
        dataType: 'json'
      }).done(function(response) {
        if (!response || response.code !== 0) {
          self.renderChartFallback(self.$tradeChart);
          self.renderChartFallback(self.$visitChart);
          self.showNotice(text.dashboardLoadFailed);
          return;
        }

        self.renderCounts(response);
        self.renderTradeChart(response.chart);
        self.renderVisitChart(response.visit_chart);
      }).fail(function() {
        self.renderChartFallback(self.$tradeChart);
        self.renderChartFallback(self.$visitChart);
        self.showNotice(text.dashboardLoadFailed);
      });
    },

    renderCounts: function(data) {
      var mappings = [
        'count1', 'count2', 'count3', 'count4', 'count5', 'count6', 'count7',
        'count8', 'count12', 'count13', 'count14', 'count15', 'count16',
        'count17', 'count18', 'count19', 'count20', 'count21', 'visit_today', 'ip_today'
      ];

      mappings.forEach(function(id) {
        if (data[id] !== undefined) {
          $('#' + id).text(data[id]);
        }
      });

      if (data.count11 !== undefined) {
        $('#count11_val').text(data.count11);
      }

      $('#pendingOrderTodo').text(data.count3 || 0);
      $('#pendingWorkorderTodo').text(data.count17 || 0);
      $('#pendingWithdrawTodo').text(data.count11 || 0);
      $('#listedGoodsTodo').text(data.count18 || 0);
    },

    startServerClock: function() {
      var self = this;
      var baseSeconds;
      var browserBase;

      if (!this.$serverTime.length) {
        return;
      }

      baseSeconds = parseInt(this.$serverTime.data('server-time'), 10);
      if (!baseSeconds) {
        return;
      }

      browserBase = Date.now();

      function tick() {
        var elapsed = Math.floor((Date.now() - browserBase) / 1000);
        self.$serverTime.text(self.formatDateTime(new Date((baseSeconds + elapsed) * 1000)));
      }

      tick();
      window.setInterval(tick, 1000);
    },

    formatDateTime: function(date) {
      function pad(value) {
        return value < 10 ? '0' + value : String(value);
      }

      return date.getFullYear() + '-' +
        pad(date.getMonth() + 1) + '-' +
        pad(date.getDate()) + ' ' +
        pad(date.getHours()) + ':' +
        pad(date.getMinutes()) + ':' +
        pad(date.getSeconds());
    },

    renderTradeChart: function(chart) {
      if (!chart || !chart.date || !chart.orders || !chart.money) {
        this.renderChartFallback(this.$tradeChart);
        return;
      }

      this.tradeChartTicks = chart.date;
      this.tradeChartSeries = [
        {
          label: text.orderSeries,
          data: chart.orders,
          color: '#2f80ed'
        },
        {
          label: text.moneySeries,
          data: chart.money,
          color: '#10b981',
          money: true
        }
      ];

      this.drawChart(this.$tradeChart, this.tradeChartSeries, this.tradeChartTicks);
    },

    renderVisitChart: function(chart) {
      if (!chart || !chart.date || !chart.visits || !chart.ips) {
        this.renderChartFallback(this.$visitChart);
        return;
      }

      this.$visitSection.removeAttr('hidden');
      this.visitChartTicks = chart.date;
      this.renderVisitRange(chart);
      this.visitChartSeries = [
        {
          label: text.visitSeries,
          data: chart.visits,
          color: '#2563eb'
        },
        {
          label: text.ipSeries,
          data: chart.ips,
          color: '#06b6d4'
        }
      ];

      this.drawChart(this.$visitChart, this.visitChartSeries, this.visitChartTicks);
    },

    renderVisitRange: function(chart) {
      var start = chart.range_start || (chart.date_full && chart.date_full[0]) || '';
      var end = chart.range_end || (chart.date_full && chart.date_full[chart.date_full.length - 1]) || '';

      if (!this.$visitRange.length) {
        return;
      }

      this.$visitRange.text(start && end ? start + ' ~ ' + end : '--');
    },

    drawChart: function($target, series, ticks) {
      var self = this;
      var options;

      if (!$target.length) {
        return;
      }

      if (typeof $.plot !== 'function') {
        self.renderChartFallback($target);
        return;
      }

      $target.empty();
      options = {
        series: {
          lines: {
            show: true,
            lineWidth: 3,
            fill: 0.12
          },
          points: {
            show: true,
            radius: 4,
            lineWidth: 2,
            fillColor: '#ffffff'
          },
          shadowSize: 0
        },
        legend: {
          show: true,
          backgroundOpacity: 0,
          labelBoxBorderColor: 'transparent',
          margin: [0, 8]
        },
        grid: {
          borderWidth: 1,
          borderColor: 'rgba(148, 163, 184, 0.16)',
          hoverable: true,
          clickable: false,
          backgroundColor: '#ffffff'
        },
        yaxis: {
          min: 0,
          tickColor: 'rgba(148, 163, 184, 0.14)',
          font: {
            color: '#64748b'
          }
        },
        xaxis: {
          ticks: ticks,
          tickColor: 'rgba(148, 163, 184, 0.14)',
          font: {
            color: '#64748b'
          }
        }
      };

      $.plot($target, series, options);

      $target.off('plothover.adminDashboard').on('plothover.adminDashboard', function(event, position, item) {
        if (!item) {
          $('#adminDashboardTooltip').remove();
          return;
        }

        self.showTooltip(item.pageX, item.pageY, item.series, item.datapoint[1]);
      });
    },

    renderChartFallback: function($target) {
      if (!$target.length) {
        return;
      }

      $target.html(
        '<div class="admin-dashboard-empty-state">' +
          '<i class="fa fa-line-chart" aria-hidden="true"></i>' +
          '<p>' + text.chartUnavailable + '</p>' +
        '</div>'
      );
    },

    showTooltip: function(x, y, series, value) {
      var amount = series.money ? '\uffe5' + value.toFixed(2) : value.toFixed(2);
      var tooltipHtml = '' +
        '<div id="adminDashboardTooltip" style="position:absolute;display:none;top:' + (y - 42) + 'px;left:' + (x + 12) + 'px;padding:8px 10px;border-radius:12px;background:#0f172a;color:#ffffff;font-size:12px;box-shadow:0 12px 24px rgba(15,23,42,0.24);z-index:99999;">' +
          this.escapeHtml(series.label) + '\uff1a' + this.escapeHtml(amount) +
        '</div>';

      $('#adminDashboardTooltip').remove();
      $(tooltipHtml).appendTo('body').fadeIn(120);
    },

    loadVisitDetails: function(page) {
      var self = this;

      self.$visitLoading.show();
      self.$visitEmpty.prop('hidden', true).html(
        '<i class="fa fa-info-circle" aria-hidden="true"></i><p>' + text.emptyVisit + '</p>'
      );
      self.$visitTable.empty();
      self.$visitPagination.empty();

      $.ajax({
        type: 'GET',
        url: 'ajax.php?act=get_visit_details',
        data: {
          page: page,
          pageSize: self.pageSize
        },
        dataType: 'json'
      }).done(function(response) {
        self.$visitLoading.hide();

        if (!response) {
          self.showVisitEmpty(text.visitError);
          return;
        }

        if (response.code === 0 && response.visits && response.visits.length) {
          self.renderVisitRows(response.visits);
          self.renderPagination(response.total, response.page, response.pageSize);
          return;
        }

        if (response.code === 1) {
          self.showVisitEmpty(response.msg || text.emptyVisit);
          return;
        }

        self.showNotice(text.requestFailed + (response.msg || text.visitError));
        self.showVisitEmpty(text.visitError);
      }).fail(function() {
        self.$visitLoading.hide();
        self.showVisitEmpty(text.visitError);
      });
    },

    renderVisitRows: function(rows) {
      var html = rows.map(function(row) {
        return '' +
          '<tr>' +
            '<td>' + dashboard.escapeHtml(row.visit_time) + '</td>' +
            '<td>' + dashboard.renderVisitAccount(row.username) + '</td>' +
            '<td>' + dashboard.escapeHtml(row.ip) + '</td>' +
            '<td class="admin-dashboard-text-break admin-dashboard-visit-url">' + dashboard.escapeHtml(row.url) + '</td>' +
            '<td>' + dashboard.escapeHtml(row.region) + '</td>' +
            '<td>' + dashboard.escapeHtml(row.visits) + '</td>' +
            '<td class="admin-dashboard-text-break">' + dashboard.escapeHtml(row.user_agent) + '</td>' +
          '</tr>';
      });

      this.$visitTable.html(html.join(''));
    },

    renderVisitAccount: function(username) {
      if (!username) {
        return '';
      }

      return '<span class="admin-dashboard-account-badge">' + this.escapeHtml(username) + '</span>';
    },

    renderPagination: function(total, current, pageSize) {
      var totalPages = Math.ceil(total / pageSize);
      var maxVisible = 5;
      var startPage = Math.max(1, current - Math.floor(maxVisible / 2));
      var endPage = Math.min(totalPages, startPage + maxVisible - 1);
      var items = [];
      var index;

      if (totalPages <= 1) {
        return;
      }

      if (endPage - startPage + 1 < maxVisible) {
        startPage = Math.max(1, endPage - maxVisible + 1);
      }

      if (startPage > 1) {
        items.push('<li><a href="#" data-page="1">' + text.firstPage + '</a></li>');
        items.push('<li class="disabled"><span>...</span></li>');
      }

      for (index = startPage; index <= endPage; index += 1) {
        items.push(
          '<li class="' + (index === current ? 'active' : '') + '">' +
            '<a href="#" data-page="' + index + '">' + index + '</a>' +
          '</li>'
        );
      }

      if (endPage < totalPages) {
        items.push('<li class="disabled"><span>...</span></li>');
        items.push('<li><a href="#" data-page="' + totalPages + '">' + text.lastPage + '</a></li>');
      }

      this.$visitPagination.html(items.join(''));
    },

    showVisitEmpty: function(message) {
      this.$visitEmpty.html(
        '<i class="fa fa-info-circle" aria-hidden="true"></i><p>' + this.escapeHtml(message) + '</p>'
      ).prop('hidden', false);
    },

    resetVisitModal: function() {
      this.$visitTable.empty();
      this.$visitPagination.empty();
      this.$visitEmpty.prop('hidden', true);
      this.$visitLoading.html(
        '<i class="fa fa-spinner fa-spin" aria-hidden="true"></i><p>' + text.loadingVisit + '</p>'
      ).show();
    },

    redrawCharts: function() {
      $('#adminDashboardTooltip').remove();

      if (this.tradeChartSeries && this.tradeChartTicks) {
        this.drawChart(this.$tradeChart, this.tradeChartSeries, this.tradeChartTicks);
      }

      if (this.visitChartSeries && this.visitChartTicks && this.$visitSection.is(':visible')) {
        this.drawChart(this.$visitChart, this.visitChartSeries, this.visitChartTicks);
      }
    },

    showNotice: function(message) {
      if (window.layer && typeof window.layer.msg === 'function') {
        window.layer.msg(message);
        return;
      }

      window.alert(message);
    },

    escapeHtml: function(value) {
      return String(value === undefined || value === null ? '' : value).replace(/[&<>"']/g, function(character) {
        var map = {
          '&': '&amp;',
          '<': '&lt;',
          '>': '&gt;',
          '"': '&quot;',
          "'": '&#39;'
        };
        return map[character];
      });
    }
  };

  $(function() {
    dashboard.init();
  });
})(window, document, window.jQuery);
