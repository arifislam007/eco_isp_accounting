document.addEventListener('DOMContentLoaded', () => {
  const charts = window.dashboardCharts;
  if (charts) {
    const collectionCanvas = document.getElementById('collectionDepositChart');
    if (collectionCanvas) {
      new Chart(collectionCanvas, {
        type: 'bar',
        data: {
          labels: charts.labels,
          datasets: [
            {
              label: 'Collection',
              data: charts.collections,
              backgroundColor: '#0d6efd',
            },
            {
              label: 'Deposit',
              data: charts.deposits,
              backgroundColor: '#20c997',
            },
          ],
        },
        options: {
          responsive: true,
          plugins: {
            legend: {
              position: 'bottom',
            },
          },
        },
      });
    }

    const profitCanvas = document.getElementById('profitChart');
    if (profitCanvas) {
      new Chart(profitCanvas, {
        type: 'line',
        data: {
          labels: charts.labels,
          datasets: [
            {
              label: 'Profit',
              data: charts.profits,
              borderColor: '#198754',
              backgroundColor: 'rgba(25, 135, 84, 0.15)',
              fill: true,
              tension: 0.35,
            },
          ],
        },
        options: {
          responsive: true,
          plugins: {
            legend: {
              position: 'bottom',
            },
          },
        },
      });
    }

    const userTrendCanvas = document.getElementById('userTrendChart');
    if (userTrendCanvas && Array.isArray(charts.users)) {
      new Chart(userTrendCanvas, {
        type: 'line',
        data: {
          labels: charts.labels,
          datasets: [
            {
              label: 'Users',
              data: charts.users,
              borderColor: '#6f42c1',
              backgroundColor: 'rgba(111, 66, 193, 0.14)',
              fill: true,
              tension: 0.3,
            },
          ],
        },
        options: {
          responsive: true,
          plugins: {
            legend: {
              position: 'bottom',
            },
          },
        },
      });
    }
  }

  wirePdfExport();
  wireBillingEditor();
  wireCostEditor();
  wireDepositEditor();
  wireDashboardRearrange();
});

function wirePdfExport() {
  const buttons = document.querySelectorAll('[data-generate-pdf]');
  if (!buttons.length || !window.jspdf || typeof window.jspdf.jsPDF !== 'function') {
    return;
  }

  buttons.forEach((button) => {
    button.addEventListener('click', async () => {
      const reportType = button.getAttribute('data-generate-pdf') || '';
      const shouldShare = button.getAttribute('data-share-pdf') === '1';

      const doc = buildPdf(reportType);
      if (!doc) {
        return;
      }

      const reportData = window.pdfReportData || {};
      const fileName = reportData.fileName || 'report.pdf';

      if (shouldShare && navigator.canShare && navigator.share) {
        const blob = doc.output('blob');
        const file = new File([blob], fileName, { type: 'application/pdf' });

        if (navigator.canShare({ files: [file] })) {
          try {
            await navigator.share({
              files: [file],
              title: reportData.title || 'Billing Report',
              text: 'Sharing billing report PDF',
            });
            return;
          } catch (error) {
            if (error && error.name === 'AbortError') {
              return;
            }
          }
        }
      }

      doc.save(fileName);
    });
  });
}

function buildPdf(reportType) {
  const { jsPDF } = window.jspdf;
  const doc = new jsPDF({ orientation: 'landscape', unit: 'pt', format: 'a4' });
  const reportData = window.pdfReportData || {};

  if (reportType === 'dashboard') {
    renderDashboardPdf(doc, reportData);
    return doc;
  }

  if (reportType === 'report') {
    renderReportPdf(doc, reportData);
    return doc;
  }

  if (reportType === 'business') {
    renderBusinessPdf(doc, reportData);
    return doc;
  }

  return null;
}

function renderDashboardPdf(doc, reportData) {
  const title = reportData.title || 'Dashboard Report';
  const cards = Array.from(document.querySelectorAll('.stat-card')).map((item) => {
    const label = (item.querySelector('.stat-label') || {}).textContent || '';
    const value = (item.querySelector('.stat-value') || {}).textContent || '';
    return [label.trim(), value.trim()];
  });

  doc.setFontSize(16);
  doc.text(title, 36, 36);
  doc.setFontSize(10);
  doc.text(`Generated: ${new Date().toLocaleString()}`, 36, 54);

  if (cards.length && doc.autoTable) {
    doc.autoTable({
      head: [['Metric', 'Value']],
      body: cards,
      startY: 70,
      theme: 'grid',
      styles: { fontSize: 9 },
      headStyles: { fillColor: [11, 19, 36] },
    });
  }

  const tableData = extractTableData('dashboardBusinessTable');
  if (tableData.head.length && doc.autoTable) {
    doc.autoTable({
      head: [tableData.head],
      body: tableData.body,
      startY: doc.lastAutoTable ? doc.lastAutoTable.finalY + 16 : 140,
      theme: 'striped',
      styles: { fontSize: 7, cellPadding: 3 },
      headStyles: { fillColor: [24, 38, 63] },
      margin: { left: 24, right: 24 },
    });
  }
}

function renderReportPdf(doc, reportData) {
  const title = reportData.title || 'Monthly Report';
  const billInfo = extractTableData('reportBillTable', { excludeHeadings: ['Action'] });
  const paymentInfo = extractTableData('reportPaymentTable');

  doc.setFontSize(16);
  doc.text(title, 36, 36);
  doc.setFontSize(10);
  doc.text(`Generated: ${new Date().toLocaleString()}`, 36, 54);

  if (billInfo.head.length && doc.autoTable) {
    doc.autoTable({
      head: [billInfo.head],
      body: billInfo.body,
      startY: 74,
      theme: 'striped',
      styles: { fontSize: 7, cellPadding: 3 },
      headStyles: { fillColor: [24, 38, 63] },
      margin: { left: 24, right: 300 },
      tableWidth: 320,
    });
  }

  if (paymentInfo.head.length && doc.autoTable) {
    doc.autoTable({
      head: [paymentInfo.head],
      body: paymentInfo.body,
      startY: 74,
      theme: 'striped',
      styles: { fontSize: 7, cellPadding: 3 },
      headStyles: { fillColor: [32, 201, 151] },
      margin: { left: 350, right: 24 },
      tableWidth: 470,
    });
  }
}

function renderBusinessPdf(doc, reportData) {
  const title = reportData.title || 'Business Report';
  const summaryRows = Array.from(document.querySelectorAll('.panel-card .text-muted.small.text-uppercase')).map((node) => {
    const valueNode = node.parentElement ? node.parentElement.querySelector('.fs-4.fw-bold') : null;
    return [node.textContent.trim(), valueNode ? valueNode.textContent.trim() : ''];
  });

  doc.setFontSize(16);
  doc.text(title, 36, 36);
  doc.setFontSize(10);
  doc.text(`Generated: ${new Date().toLocaleString()}`, 36, 54);

  if (summaryRows.length && doc.autoTable) {
    doc.autoTable({
      head: [['Field', 'Value']],
      body: summaryRows,
      startY: 70,
      theme: 'grid',
      styles: { fontSize: 9 },
      headStyles: { fillColor: [11, 19, 36] },
    });
  }

  const deposits = extractTableData('businessDepositTable', { excludeHeadings: ['Action'] });
  if (deposits.head.length && doc.autoTable) {
    doc.autoTable({
      head: [deposits.head],
      body: deposits.body,
      startY: doc.lastAutoTable ? doc.lastAutoTable.finalY + 16 : 180,
      theme: 'striped',
      styles: { fontSize: 8, cellPadding: 4 },
      headStyles: { fillColor: [24, 38, 63] },
      margin: { left: 36, right: 36 },
    });
  }
}

function extractTableData(tableId, options = {}) {
  const table = document.getElementById(tableId);
  if (!table) {
    return { head: [], body: [] };
  }

  const excludeHeadings = Array.isArray(options.excludeHeadings) ? options.excludeHeadings : [];
  const headings = Array.from(table.querySelectorAll('thead th')).map((th) => th.textContent.trim());
  const keepIndexes = headings
    .map((heading, index) => (excludeHeadings.includes(heading) ? null : index))
    .filter((index) => index !== null);

  const head = keepIndexes.map((index) => headings[index]);
  const body = Array.from(table.querySelectorAll('tbody tr')).map((row) => {
    const cells = Array.from(row.querySelectorAll('td')).map((cell) => cell.textContent.trim());
    return keepIndexes.map((index) => cells[index] || '');
  });

  return { head, body };
}

function wireDepositEditor() {
  const buttons = document.querySelectorAll('[data-edit-deposit]');
  if (!buttons.length) {
    return;
  }

  const idInput = document.getElementById('editDepositId');
  const businessInput = document.getElementById('editBusinessId');
  const dateInput = document.getElementById('editDepositDate');
  const amountInput = document.getElementById('editDepositAmount');
  const typeInput = document.getElementById('editDepositType');
  const mediumInput = document.getElementById('editDepositMedium');
  const referenceInput = document.getElementById('editDepositReference');
  const discountInput = document.getElementById('editDepositDiscount');

  if (!idInput || !businessInput || !dateInput || !amountInput || !typeInput || !mediumInput || !referenceInput) {
    return;
  }

  buttons.forEach((button) => {
    button.addEventListener('click', () => {
      idInput.value = button.getAttribute('data-deposit-id') || '';
      businessInput.value = button.getAttribute('data-business-id') || '';
      dateInput.value = button.getAttribute('data-date') || '';
      amountInput.value = button.getAttribute('data-amount') || '';
      typeInput.value = button.getAttribute('data-type') || 'deposit';
      const mediumValue = (button.getAttribute('data-medium') || 'Bank').toLowerCase();
      if (mediumValue === 'bank') {
        mediumInput.value = 'Bank';
      } else if (mediumValue === 'bkash') {
        mediumInput.value = 'bKash';
      } else if (mediumValue === 'cash') {
        mediumInput.value = 'cash';
      } else {
        mediumInput.value = button.getAttribute('data-medium') || 'Bank';
      }
      referenceInput.value = button.getAttribute('data-reference') || '';
      if (discountInput) {
        discountInput.value = button.getAttribute('data-discount') || '0';
      }
    });
  });
}

function wireBillingEditor() {
  const buttons = document.querySelectorAll('[data-edit-billing]');
  if (!buttons.length) {
    return;
  }

  const idInput = document.getElementById('editBillingBusinessId');
  const nameInput = document.getElementById('editBillingBusinessName');
  const monthInput = document.getElementById('editBillingMonth');
  const usersInput = document.getElementById('editBillingUsers');
  const collectionInput = document.getElementById('editBillingCollection');
  const commissionInput = document.getElementById('editBillingCommission');
  const bonusInput = document.getElementById('editBillingBonus');
  const discountInput = document.getElementById('editBillingDiscount');

  if (!idInput || !nameInput || !monthInput || !usersInput || !collectionInput || !commissionInput || !bonusInput || !discountInput) {
    return;
  }

  buttons.forEach((button) => {
    button.addEventListener('click', () => {
      idInput.value = button.getAttribute('data-business-id') || '';
      nameInput.value = button.getAttribute('data-business-name') || '';
      monthInput.value = button.getAttribute('data-month') || '';
      usersInput.value = button.getAttribute('data-users') || '0';
      collectionInput.value = button.getAttribute('data-collection') || '0';
      commissionInput.value = button.getAttribute('data-commission') || '0';
      bonusInput.value = button.getAttribute('data-bonus') || '0';
      discountInput.value = button.getAttribute('data-discount') || '0';
    });
  });
}

function wireCostEditor() {
  const buttons = document.querySelectorAll('[data-edit-cost]');
  if (!buttons.length) {
    return;
  }

  const idInput = document.getElementById('editCostId');
  const typeInput = document.getElementById('editCostType');
  const amountInput = document.getElementById('editCostAmount');
  const monthInput = document.getElementById('editCostMonth');

  if (!idInput || !typeInput || !amountInput || !monthInput) {
    return;
  }

  buttons.forEach((button) => {
    button.addEventListener('click', () => {
      idInput.value = button.getAttribute('data-cost-id') || '';
      typeInput.value = button.getAttribute('data-cost-type') || '';
      amountInput.value = button.getAttribute('data-cost-amount') || '0';
      monthInput.value = button.getAttribute('data-cost-month') || '';
    });
  });
}

function wireDashboardRearrange() {
  const container = document.getElementById('dashboardLayout');
  if (!container) {
    return;
  }

  const widgets = Array.from(container.querySelectorAll('.dashboard-widget'));
  if (!widgets.length) {
    return;
  }

  const toggleBtn = document.getElementById('toggleRearrangeBtn');
  const resetBtn = document.getElementById('resetDashboardLayoutBtn');
  const storageKey = (window.dashboardLayoutConfig && window.dashboardLayoutConfig.storageKey) || 'dashboardLayoutOrder';
  let enabled = false;
  let dragId = '';

  applySavedOrder(container, storageKey);

  if (toggleBtn) {
    toggleBtn.addEventListener('click', () => {
      enabled = !enabled;
      toggleBtn.textContent = enabled ? 'Done Rearranging' : 'Rearrange Dashboard';
      container.classList.toggle('rearrange-enabled', enabled);

      container.querySelectorAll('.dashboard-widget').forEach((widget) => {
        widget.draggable = enabled;
      });

      updateWidgetControls(container, enabled);
    });
  }

  if (resetBtn) {
    resetBtn.addEventListener('click', () => {
      localStorage.removeItem(storageKey);
      window.location.reload();
    });
  }

  widgets.forEach((widget) => {
    widget.addEventListener('dragstart', (event) => {
      if (!enabled) {
        event.preventDefault();
        return;
      }

      dragId = widget.getAttribute('data-widget-id') || '';
      widget.classList.add('is-dragging');
      if (event.dataTransfer) {
        event.dataTransfer.effectAllowed = 'move';
      }
    });

    widget.addEventListener('dragend', () => {
      widget.classList.remove('is-dragging');
      container.querySelectorAll('.dashboard-widget').forEach((w) => w.classList.remove('drag-over'));
    });

    widget.addEventListener('dragover', (event) => {
      if (!enabled || !dragId) {
        return;
      }

      event.preventDefault();
      widget.classList.add('drag-over');
    });

    widget.addEventListener('dragleave', () => {
      widget.classList.remove('drag-over');
    });

    widget.addEventListener('drop', (event) => {
      if (!enabled || !dragId) {
        return;
      }

      event.preventDefault();
      widget.classList.remove('drag-over');

      const dragged = container.querySelector(`.dashboard-widget[data-widget-id="${dragId}"]`);
      if (!dragged || dragged === widget) {
        return;
      }

      container.insertBefore(dragged, widget);
      persistOrder(container, storageKey);
    });
  });

  container.addEventListener('click', (event) => {
    const target = event.target;
    if (!(target instanceof HTMLElement)) {
      return;
    }

    const moveUp = target.closest('.widget-move-up');
    const moveDown = target.closest('.widget-move-down');
    if (!moveUp && !moveDown) {
      return;
    }

    if (!enabled) {
      return;
    }

    const widget = target.closest('.dashboard-widget');
    if (!widget) {
      return;
    }

    if (moveUp) {
      const previous = widget.previousElementSibling;
      if (previous) {
        container.insertBefore(widget, previous);
        persistOrder(container, storageKey);
      }
    }

    if (moveDown) {
      const next = widget.nextElementSibling;
      if (next) {
        container.insertBefore(next, widget);
        persistOrder(container, storageKey);
      }
    }
  });
}

function updateWidgetControls(container, enabled) {
  container.querySelectorAll('.dashboard-widget-meta').forEach((meta) => {
    meta.classList.toggle('d-none', !enabled);
  });
}

function applySavedOrder(container, storageKey) {
  const raw = localStorage.getItem(storageKey);
  if (!raw) {
    return;
  }

  let order;
  try {
    order = JSON.parse(raw);
  } catch (error) {
    return;
  }

  if (!Array.isArray(order)) {
    return;
  }

  order.forEach((id) => {
    const node = container.querySelector(`.dashboard-widget[data-widget-id="${id}"]`);
    if (node) {
      container.appendChild(node);
    }
  });
}

function persistOrder(container, storageKey) {
  const order = Array.from(container.querySelectorAll('.dashboard-widget')).map((widget) => widget.getAttribute('data-widget-id'));
  localStorage.setItem(storageKey, JSON.stringify(order));
}
