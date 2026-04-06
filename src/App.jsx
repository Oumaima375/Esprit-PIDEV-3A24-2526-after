import { useMemo, useState } from 'react';
import {
  BedDouble,
  CalendarDays,
  CircleDollarSign,
  CreditCard,
  FilePenLine,
  FilterX,
  Globe2,
  Hotel,
  MapPin,
  Plane,
  Printer,
  Search,
  Settings,
  Trash2,
  Umbrella,
  Users,
  Wallet,
  X,
} from 'lucide-react';

const reservationsData = [
  {
    id: 102,
    destination: 'Djerba',
    date: '2026-04-14',
    type: 'Plage',
    people: 2,
    price: '1 200,00 TND',
    status: 'Confirmée',
    description: 'Séjour détente avec transfert aéroport inclus.',
  },
  {
    id: 107,
    destination: 'Tozeur',
    date: '2026-04-20',
    type: 'Safari',
    people: 4,
    price: '3 450,00 TND',
    status: 'En attente',
    description: 'Excursion désert premium avec nuit en campement.',
  },
  {
    id: 115,
    destination: 'Hammamet',
    date: '2026-05-03',
    type: 'Hôtel',
    people: 3,
    price: '2 100,00 TND',
    status: 'Confirmée',
    description: 'Week-end resort 5 étoiles avec demi-pension.',
  },
  {
    id: 118,
    destination: 'Zanzibar',
    date: '2026-05-16',
    type: 'Plage',
    people: 2,
    price: '6 800,00 TND',
    status: 'En attente',
    description: 'Circuit balnéaire avec excursions marines.',
  },
  {
    id: 124,
    destination: 'Kenya',
    date: '2026-06-08',
    type: 'Safari',
    people: 5,
    price: '7 120,00 TND',
    status: 'Confirmée',
    description: 'Safari privé avec lodge et pension complète.',
  },
  {
    id: 129,
    destination: 'Sousse',
    date: '2026-06-12',
    type: 'Hôtel',
    people: 2,
    price: '1 580,00 TND',
    status: 'Confirmée',
    description: 'Séjour urbain et spa au bord de mer.',
  },
];

const paymentsData = [
  {
    ref: 'PAY-001',
    trip: 'Ref #102 - Djerba',
    status: 'Payé',
    amount: '1 200,00 EUR',
  },
  {
    ref: 'PAY-002',
    trip: 'Ref #107 - Tozeur',
    status: 'En attente',
    amount: '3 450,00 TND',
  },
  {
    ref: 'PAY-003',
    trip: 'Ref #115 - Hammamet',
    status: 'Remboursé',
    amount: '2 100,00 TND',
  },
  {
    ref: 'PAY-004',
    trip: 'Ref #124 - Kenya',
    status: 'Payé',
    amount: '7 120,00 TND',
  },
];

const reservationStats = [
  { value: '10', label: 'Total Réservations', color: 'text-brand', tone: 'bg-brand/10' },
  { value: '6', label: 'Confirmées', color: 'text-success', tone: 'bg-success/10' },
  { value: '4', label: 'En Attente', color: 'text-warning', tone: 'bg-warning/10' },
];

const paymentStats = [
  { value: '8', label: 'Nombre de paiements', icon: Wallet },
  { value: '20 670,00 TND', label: 'Montant Total Versé', icon: CircleDollarSign },
];

const statusClasses = {
  Confirmée: 'bg-success/10 text-success',
  'En attente': 'bg-warning/10 text-warning',
  Payé: 'bg-success/10 text-success',
  Remboursé: 'bg-slate-200 text-slate-600',
};

const typeIcons = {
  Hôtel: Hotel,
  Plage: Umbrella,
  Safari: Globe2,
};

function App() {
  const [activeView, setActiveView] = useState('reservations');
  const [showReservationModal, setShowReservationModal] = useState(false);
  const [showPaymentModal, setShowPaymentModal] = useState(false);
  const [search, setSearch] = useState('');
  const [startDate, setStartDate] = useState('');
  const [endDate, setEndDate] = useState('');

  const filteredReservations = useMemo(() => {
    return reservationsData.filter((reservation) => {
      const query = search.trim().toLowerCase();
      const matchesQuery =
        !query ||
        reservation.destination.toLowerCase().includes(query) ||
        reservation.type.toLowerCase().includes(query) ||
        String(reservation.id).includes(query);

      const matchesStart = !startDate || reservation.date >= startDate;
      const matchesEnd = !endDate || reservation.date <= endDate;
      return matchesQuery && matchesStart && matchesEnd;
    });
  }, [search, startDate, endDate]);

  return (
    <div className="min-h-screen bg-surface">
      <div className="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
        <Header
          activeView={activeView}
          onChangeView={setActiveView}
          onNewReservation={() => setShowReservationModal(true)}
          onPayments={() => setActiveView('payments')}
        />

        <main className="mt-6 space-y-6">
          {activeView === 'reservations' ? (
            <>
              <StatsGrid items={reservationStats} />
              <SearchBar
                search={search}
                startDate={startDate}
                endDate={endDate}
                onSearchChange={setSearch}
                onStartDateChange={setStartDate}
                onEndDateChange={setEndDate}
                onReset={() => {
                  setSearch('');
                  setStartDate('');
                  setEndDate('');
                }}
              />
              <ReservationGrid items={filteredReservations} />
            </>
          ) : (
            <PaymentsView onNewPayment={() => setShowPaymentModal(true)} />
          )}
        </main>
      </div>

      <ReservationModal
        open={showReservationModal}
        onClose={() => setShowReservationModal(false)}
      />
      <PaymentModal open={showPaymentModal} onClose={() => setShowPaymentModal(false)} />
    </div>
  );
}

function Header({ activeView, onChangeView, onNewReservation, onPayments }) {
  return (
    <header className="overflow-hidden rounded-[32px] bg-hero px-6 py-6 text-white shadow-soft sm:px-8">
      <div className="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
        <div className="flex flex-col gap-4">
          <div className="flex items-center gap-3">
            <div className="flex h-14 w-14 items-center justify-center rounded-2xl bg-white/15 backdrop-blur-sm">
              <Plane className="h-7 w-7" />
            </div>
            <div>
              <p className="text-xs uppercase tracking-[0.35em] text-white/70">Travel Dashboard</p>
              <h1 className="text-2xl font-extrabold sm:text-3xl">AFTER Travel</h1>
            </div>
          </div>

          <nav className="flex flex-wrap gap-3">
            <button
              type="button"
              onClick={() => onChangeView('reservations')}
              className={`action-button ${
                activeView === 'reservations'
                  ? 'bg-white text-brand'
                  : 'border border-white/25 bg-white/10 text-white'
              }`}
            >
              <BedDouble className="h-4 w-4" />
              Réservations
            </button>
            <button
              type="button"
              onClick={onPayments}
              className={`action-button ${
                activeView === 'payments'
                  ? 'bg-white text-brand'
                  : 'border border-white/25 bg-white/10 text-white'
              }`}
            >
              <CreditCard className="h-4 w-4" />
              Paiements
            </button>
          </nav>
        </div>

        <div className="flex flex-wrap gap-3">
          <button
            type="button"
            onClick={onNewReservation}
            className="action-button bg-sky-400 text-white hover:bg-sky-300"
          >
            + Réserver
          </button>
          <button
            type="button"
            onClick={onPayments}
            className="action-button border border-white/60 bg-transparent text-white hover:bg-white/10"
          >
            Mes Paiements
          </button>
          <button
            type="button"
            className="action-button border border-white/20 bg-white/10 text-white hover:bg-white/20"
          >
            <Settings className="h-4 w-4" />
            Admin
          </button>
        </div>
      </div>
    </header>
  );
}

function StatsGrid({ items }) {
  return (
    <section className="grid gap-4 md:grid-cols-3">
      {items.map((item) => (
        <article key={item.label} className="panel p-5">
          <div className={`inline-flex rounded-2xl px-3 py-1 text-xs font-semibold ${item.tone} ${item.color}`}>
            Aperçu
          </div>
          <div className="mt-5 flex items-end justify-between gap-4">
            <div>
              <p className={`text-3xl font-extrabold ${item.color}`}>{item.value}</p>
              <p className="mt-1 text-sm font-medium text-slate-500">{item.label}</p>
            </div>
          </div>
        </article>
      ))}
    </section>
  );
}

function SearchBar({
  search,
  startDate,
  endDate,
  onSearchChange,
  onStartDateChange,
  onEndDateChange,
  onReset,
}) {
  return (
    <section className="panel p-4">
      <div className="grid gap-3 lg:grid-cols-[1.4fr,1fr,1fr,auto]">
        <label className="relative">
          <Search className="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
          <input
            className="field pl-11"
            type="text"
            placeholder="Recherche destination, type ou référence"
            value={search}
            onChange={(event) => onSearchChange(event.target.value)}
          />
        </label>
        <input
          className="field"
          type="date"
          value={startDate}
          onChange={(event) => onStartDateChange(event.target.value)}
        />
        <input
          className="field"
          type="date"
          value={endDate}
          onChange={(event) => onEndDateChange(event.target.value)}
        />
        <button
          type="button"
          onClick={onReset}
          className="action-button border border-slate-200 bg-slate-50 text-slate-600 hover:border-brand/20 hover:text-brand"
        >
          <FilterX className="h-4 w-4" />
          Réinitialiser
        </button>
      </div>
    </section>
  );
}

function ReservationGrid({ items }) {
  return (
    <section className="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
      {items.map((reservation) => {
        const TypeIcon = typeIcons[reservation.type] || Globe2;

        return (
          <article key={reservation.id} className="panel overflow-hidden">
            <div className="border-b border-slate-100 bg-slate-50/70 px-5 py-4">
              <div className="flex items-center justify-between gap-3">
                <div>
                  <p className="text-sm font-semibold text-brand">Ref #{reservation.id}</p>
                  <h3 className="mt-1 text-lg font-bold text-slate-900">{reservation.destination}</h3>
                </div>
                <span
                  className={`rounded-full px-3 py-1 text-xs font-semibold ${
                    statusClasses[reservation.status]
                  }`}
                >
                  {reservation.status}
                </span>
              </div>
            </div>

            <div className="space-y-4 px-5 py-5">
              <Detail icon={MapPin} label="Destination" value={reservation.destination} />
              <Detail icon={CalendarDays} label="Date" value={formatDate(reservation.date)} />
              <Detail icon={TypeIcon} label="Type" value={reservation.type} />
              <Detail icon={Users} label="Personnes" value={`${reservation.people} voyageurs`} />

              <p className="rounded-2xl bg-brand-mist px-4 py-3 text-sm text-slate-500">
                {reservation.description}
              </p>

              <div className="flex items-center justify-between pt-2">
                <p className="text-xl font-extrabold text-slate-900">{reservation.price}</p>
                <button
                  type="button"
                  className="rounded-2xl bg-sand/15 px-4 py-2 text-sm font-semibold text-sand transition hover:bg-sand hover:text-white"
                >
                  Détails
                </button>
              </div>
            </div>
          </article>
        );
      })}
    </section>
  );
}

function Detail({ icon: Icon, label, value }) {
  return (
    <div className="flex items-center gap-3">
      <div className="flex h-11 w-11 items-center justify-center rounded-2xl bg-brand/10 text-brand">
        <Icon className="h-5 w-5" />
      </div>
      <div>
        <p className="text-xs font-semibold uppercase tracking-wide text-slate-400">{label}</p>
        <p className="text-sm font-semibold text-slate-700">{value}</p>
      </div>
    </div>
  );
}

function PaymentsView({ onNewPayment }) {
  return (
    <section className="space-y-6">
      <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <p className="text-sm font-semibold uppercase tracking-[0.25em] text-brand">Mes Paiements</p>
          <h2 className="mt-1 text-3xl font-extrabold text-slate-900">Suivi financier</h2>
        </div>
        <button
          type="button"
          onClick={onNewPayment}
          className="action-button bg-brand text-white hover:bg-brand-dark"
        >
          + Nouveau
        </button>
      </div>

      <div className="grid gap-4 lg:grid-cols-2">
        {paymentStats.map((item) => {
          const Icon = item.icon;
          return (
            <article key={item.label} className="panel p-6">
              <div className="flex items-center justify-between gap-4">
                <div>
                  <p className="text-sm font-semibold text-slate-500">{item.label}</p>
                  <p className="mt-3 text-3xl font-extrabold text-brand">{item.value}</p>
                </div>
                <div className="flex h-16 w-16 items-center justify-center rounded-3xl bg-brand/10 text-brand">
                  <Icon className="h-8 w-8" />
                </div>
              </div>
            </article>
          );
        })}
      </div>

      <div className="panel p-4 sm:p-5">
        <div className="mb-4 flex items-center justify-between gap-3">
          <div>
            <h3 className="text-lg font-bold text-slate-900">Historique</h3>
            <p className="text-sm text-slate-500">Toutes les transactions récentes et leurs statuts.</p>
          </div>
        </div>

        <div className="space-y-3">
          {paymentsData.map((payment) => (
            <article
              key={payment.ref}
              className="flex flex-col gap-4 rounded-3xl border border-slate-100 bg-white px-4 py-4 sm:flex-row sm:items-center sm:justify-between"
            >
              <div className="flex min-w-0 items-center gap-3">
                <div className="flex h-12 w-12 items-center justify-center rounded-2xl bg-brand/10 text-brand">
                  <Wallet className="h-5 w-5" />
                </div>
                <div className="min-w-0">
                  <p className="font-bold text-slate-900">{payment.ref}</p>
                  <p className="truncate text-sm text-slate-500">{payment.trip}</p>
                </div>
              </div>

              <div className="flex flex-wrap items-center gap-3">
                <span className={`rounded-full px-3 py-1 text-xs font-semibold ${statusClasses[payment.status]}`}>
                  {payment.status}
                </span>
                <span className="rounded-full bg-brand px-3 py-1 text-xs font-semibold text-white">
                  {payment.amount}
                </span>
                <div className="flex items-center gap-2">
                  <IconButton icon={Printer} />
                  <IconButton icon={FilePenLine} />
                  <IconButton icon={Trash2} danger />
                </div>
              </div>
            </article>
          ))}
        </div>
      </div>
    </section>
  );
}

function IconButton({ icon: Icon, danger = false }) {
  return (
    <button
      type="button"
      className={`flex h-10 w-10 items-center justify-center rounded-2xl border transition ${
        danger
          ? 'border-red-100 bg-red-50 text-red-500 hover:bg-red-500 hover:text-white'
          : 'border-slate-100 bg-slate-50 text-slate-500 hover:bg-brand hover:text-white'
      }`}
    >
      <Icon className="h-4 w-4" />
    </button>
  );
}

function ModalShell({ open, title, subtitle, icon: Icon, children, onClose }) {
  if (!open) return null;

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/45 px-4 py-8 backdrop-blur-sm">
      <div className="w-full max-w-3xl overflow-hidden rounded-[32px] bg-white shadow-soft">
        <div className="bg-hero px-6 py-6 text-white">
          <div className="flex items-start justify-between gap-4">
            <div className="flex items-center gap-4">
              <div className="flex h-16 w-16 items-center justify-center rounded-3xl bg-white/15">
                <Icon className="h-8 w-8" />
              </div>
              <div>
                <h3 className="text-2xl font-bold">{title}</h3>
                <p className="mt-1 text-sm text-white/75">{subtitle}</p>
              </div>
            </div>
            <button
              type="button"
              onClick={onClose}
              className="flex h-11 w-11 items-center justify-center rounded-2xl bg-white/10 text-white transition hover:bg-white/20"
            >
              <X className="h-5 w-5" />
            </button>
          </div>
        </div>
        <div className="px-6 py-6">{children}</div>
      </div>
    </div>
  );
}

function ReservationModal({ open, onClose }) {
  const handleSubmit = (event) => {
    event.preventDefault();
    onClose();
  };

  return (
    <ModalShell
      open={open}
      onClose={onClose}
      title="Nouvelle Réservation"
      subtitle="Créez une nouvelle fiche voyage client."
      icon={Plane}
    >
      <form className="space-y-5" onSubmit={handleSubmit}>
        <div className="grid gap-4 md:grid-cols-2">
          <FormField label="Voyage ID" type="text" placeholder="VG-1024" />
          <FormField label="Client ID" type="text" placeholder="CL-980" />
          <FormField label="Date du voyage" type="date" />
          <FormField label="Nb Personnes" type="number" placeholder="2" />
          <FormField label="Prix Total (TND)" type="number" placeholder="1200" />
          <FormSelect
            label="Type"
            options={['Hôtel', 'Plage', 'Safari']}
          />
          <div className="md:col-span-2">
            <FormField label="Lieu" type="text" placeholder="Destination ou lieu du séjour" />
          </div>
          <div className="md:col-span-2">
            <label className="block space-y-2">
              <span className="text-sm font-semibold text-slate-700">Description</span>
              <textarea
                className="field min-h-32 resize-none"
                placeholder="Ajoutez les détails utiles de la réservation..."
              />
            </label>
          </div>
        </div>

        <ModalActions onClose={onClose} />
      </form>
    </ModalShell>
  );
}

function PaymentModal({ open, onClose }) {
  const handleSubmit = (event) => {
    event.preventDefault();
    onClose();
  };

  return (
    <ModalShell
      open={open}
      onClose={onClose}
      title="Effectuer un paiement"
      subtitle="Enregistrez un paiement ou mettez à jour son statut."
      icon={Wallet}
    >
      <form className="space-y-5" onSubmit={handleSubmit}>
        <div className="grid gap-4 md:grid-cols-2">
          <FormField label="Référence" type="text" placeholder="PAY-009" />
          <FormField label="Montant" type="number" placeholder="1200" />
          <FormSelect label="Devise" options={['TND', 'EUR']} />
          <FormSelect label="Méthode" options={['Carte bancaire', 'Virement', 'Espèces']} />
          <FormSelect label="Statut" options={['Payé', 'En attente']} />
          <FormField label="Numéro de Réservation" type="text" placeholder="REF-102" />
          <div className="md:col-span-2">
            <FormField label="Date" type="date" />
          </div>
        </div>

        <ModalActions onClose={onClose} />
      </form>
    </ModalShell>
  );
}

function FormField({ label, type, placeholder = '' }) {
  return (
    <label className="block space-y-2">
      <span className="text-sm font-semibold text-slate-700">{label}</span>
      <input className="field" type={type} placeholder={placeholder} />
    </label>
  );
}

function FormSelect({ label, options }) {
  return (
    <label className="block space-y-2">
      <span className="text-sm font-semibold text-slate-700">{label}</span>
      <select className="field">
        {options.map((option) => (
          <option key={option} value={option}>
            {option}
          </option>
        ))}
      </select>
    </label>
  );
}

function ModalActions({ onClose }) {
  return (
    <div className="flex flex-col-reverse gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:justify-end">
      <button
        type="button"
        onClick={onClose}
        className="action-button border border-slate-300 bg-transparent text-slate-600 hover:border-brand hover:text-brand"
      >
        Annuler
      </button>
      <button
        type="submit"
        className="action-button bg-brand text-white hover:bg-brand-dark"
      >
        Enregistrer
      </button>
    </div>
  );
}

function formatDate(date) {
  return new Intl.DateTimeFormat('fr-FR', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
  }).format(new Date(date));
}

export default App;
