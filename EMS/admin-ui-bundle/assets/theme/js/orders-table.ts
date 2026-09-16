import type { JQueryStatic } from 'jquery';

export function initOrdersTable($: JQueryStatic): void {
  const table = document.getElementById('ordersTable');
  if (!table) {
    return;
  }

  $(table).DataTable({
    pageLength: 5,
    lengthMenu: [5, 10, 25],
    language: {
      search: 'Zoeken:',
      lengthMenu: '_MENU_ per pagina',
      info: 'Toont _START_ tot _END_ van _TOTAL_ bestellingen',
      infoEmpty: 'Geen bestellingen gevonden',
      infoFiltered: '(gefilterd van _MAX_ totaal)',
      zeroRecords: 'Geen overeenkomstige bestellingen gevonden',
      paginate: {
        first: 'Eerste',
        last: 'Laatste',
        next: 'Volgende',
        previous: 'Vorige',
      },
    },
  });
}
