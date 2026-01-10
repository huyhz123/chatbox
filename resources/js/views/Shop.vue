<template>
  <div class="min-h-screen bg-base-200">
    <Header />

    <div class="container mx-auto px-4 py-6">
      <!-- Header -->
      <div class="text-center mb-8">
        <h1 class="text-4xl font-bold mb-2">🛒 Shop</h1>
        <p class="text-base-content/70">Purchase coins and VIP packages</p>
        <div class="mt-4">
          <div class="badge badge-lg badge-primary gap-2">
            💰 Your Balance: <span class="font-bold">{{ formatNumber(userBalance) }}</span> coins
          </div>
        </div>
      </div>

      <!-- Tabs -->
      <div class="tabs tabs-boxed mb-6 bg-base-100 shadow-xl p-2 justify-center">
        <a
          class="tab"
          :class="{ 'tab-active': activeTab === 'coins' }"
          @click="activeTab = 'coins'"
        >
          💰 Coins
        </a>
        <a
          class="tab"
          :class="{ 'tab-active': activeTab === 'vip' }"
          @click="activeTab = 'vip'"
        >
          👑 VIP Packages
        </a>
        <a
          class="tab"
          :class="{ 'tab-active': activeTab === 'gifts' }"
          @click="activeTab = 'gifts'"
        >
          🎁 Gift Packs
        </a>
      </div>

      <!-- Coin Packages -->
      <div v-if="activeTab === 'coins'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div
          v-for="pkg in coinPackages"
          :key="pkg.id"
          class="card bg-base-100 shadow-xl hover:shadow-2xl transition-shadow"
        >
          <div class="card-body items-center text-center">
            <div class="text-6xl mb-4">💰</div>
            <h3 class="card-title text-2xl">{{ formatNumber(pkg.coins) }} Coins</h3>
            <div v-if="pkg.bonus" class="badge badge-success gap-1">
              +{{ pkg.bonus }}% Bonus
            </div>
            <div class="text-3xl font-bold text-primary mt-4">
              {{ formatCurrency(pkg.price) }}
            </div>
            <div class="card-actions justify-center mt-6 w-full">
              <button class="btn btn-primary btn-block" @click="purchaseCoinPackage(pkg)">
                Purchase
              </button>
            </div>
            <div class="flex gap-2 mt-4">
              <button class="btn btn-sm btn-ghost">
                <img src="https://cdn-icons-png.flaticon.com/512/5968/5968242.png" class="w-5 h-5" alt="VNPAY" />
              </button>
              <button class="btn btn-sm btn-ghost">
                <img src="https://cdn-icons-png.flaticon.com/512/5968/5968764.png" class="w-5 h-5" alt="MoMo" />
              </button>
              <button class="btn btn-sm btn-ghost">
                <img src="https://cdn-icons-png.flaticon.com/512/5968/5968382.png" class="w-5 h-5" alt="ZaloPay" />
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- VIP Packages -->
      <div v-if="activeTab === 'vip'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <div
          v-for="vip in vipPackages"
          :key="vip.id"
          class="card bg-base-100 shadow-xl hover:shadow-2xl transition-all hover:-translate-y-1"
          :class="{ 'ring-4 ring-warning': vip.is_popular }"
        >
          <div class="card-body">
            <div v-if="vip.is_popular" class="badge badge-warning absolute top-4 right-4">Popular</div>
            <div class="text-center">
              <div class="text-5xl mb-2">{{ vip.icon }}</div>
              <h3 class="card-title justify-center">{{ vip.name }}</h3>
              <div class="badge badge-warning mt-2">VIP {{ vip.level }}</div>
            </div>

            <div class="divider"></div>

            <ul class="space-y-2 text-sm">
              <li v-for="(benefit, index) in vip.benefits" :key="index" class="flex items-start gap-2">
                <svg class="w-5 h-5 text-success flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span>{{ benefit }}</span>
              </li>
            </ul>

            <div class="divider"></div>

            <div class="text-center">
              <div class="text-2xl font-bold text-primary">
                {{ formatCurrency(vip.price) }}
              </div>
              <div class="text-xs text-base-content/70">/ {{ vip.duration }} days</div>
            </div>

            <button class="btn btn-primary btn-block mt-4" @click="purchaseVIP(vip)">
              Purchase VIP
            </button>
          </div>
        </div>
      </div>

      <!-- Gift Packs -->
      <div v-if="activeTab === 'gifts'" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
        <div
          v-for="gift in giftPacks"
          :key="gift.id"
          class="card bg-base-100 shadow-xl hover:shadow-2xl transition-shadow cursor-pointer"
          @click="purchaseGift(gift)"
        >
          <figure class="pt-6">
            <div class="text-5xl">{{ gift.icon }}</div>
          </figure>
          <div class="card-body p-4 text-center">
            <h3 class="font-bold text-sm">{{ gift.name }}</h3>
            <div class="badge badge-primary badge-sm mx-auto">{{ gift.quantity }}x</div>
            <div class="font-bold text-primary mt-2">
              {{ formatNumber(gift.coins) }} 💰
            </div>
            <button class="btn btn-primary btn-xs mt-2" @click.stop="purchaseGift(gift)">
              Buy Pack
            </button>
          </div>
        </div>
      </div>

      <!-- Purchase History -->
      <div class="card bg-base-100 shadow-xl mt-8">
        <div class="card-body">
          <h2 class="card-title">📜 Purchase History</h2>
          <div class="overflow-x-auto">
            <table class="table">
              <thead>
                <tr>
                  <th>Date</th>
                  <th>Item</th>
                  <th>Amount</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(transaction, index) in purchaseHistory" :key="index" class="hover">
                  <td>{{ formatDate(transaction.date) }}</td>
                  <td>{{ transaction.item }}</td>
                  <td class="font-bold">{{ formatCurrency(transaction.amount) }}</td>
                  <td>
                    <div class="badge" :class="transaction.status === 'completed' ? 'badge-success' : 'badge-warning'">
                      {{ transaction.status }}
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useAuthStore } from '@/stores/auth';
import { useNotificationStore } from '@/stores/notification';
import Header from '@/components/common/Header.vue';

const authStore = useAuthStore();
const notificationStore = useNotificationStore();

const activeTab = ref('coins');
const coinPackages = ref([]);
const vipPackages = ref([]);
const giftPacks = ref([]);
const purchaseHistory = ref([]);

const userBalance = ref(authStore.userBalance || 10000);

const formatNumber = (num) => {
  return new Intl.NumberFormat().format(num);
};

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount);
};

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('vi-VN', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
  });
};

const purchaseCoinPackage = (pkg) => {
  notificationStore.info(`Redirecting to payment gateway for ${formatNumber(pkg.coins)} coins...`);
  // TODO: Integrate with payment gateway
};

const purchaseVIP = (vip) => {
  notificationStore.info(`Purchasing ${vip.name} VIP package...`);
  // TODO: Integrate with payment gateway
};

const purchaseGift = (gift) => {
  notificationStore.info(`Purchasing ${gift.name} gift pack...`);
  // TODO: Purchase gift pack
};

onMounted(() => {
  // TODO: Fetch packages from API
  coinPackages.value = [
    { id: 1, coins: 1000, price: 20000, bonus: 0 },
    { id: 2, coins: 5000, price: 90000, bonus: 10 },
    { id: 3, coins: 10000, price: 170000, bonus: 15 },
    { id: 4, coins: 50000, price: 800000, bonus: 20 },
    { id: 5, coins: 100000, price: 1500000, bonus: 25 },
    { id: 6, coins: 500000, price: 7000000, bonus: 30 },
  ];

  vipPackages.value = [
    {
      id: 1,
      name: 'Bronze',
      level: 1,
      icon: '🥉',
      price: 50000,
      duration: 30,
      is_popular: false,
      benefits: ['Special badge', '+10% coin bonus', 'Priority support'],
    },
    {
      id: 2,
      name: 'Silver',
      level: 2,
      icon: '🥈',
      price: 100000,
      duration: 30,
      is_popular: false,
      benefits: ['Special badge', '+15% coin bonus', 'Priority support', 'Exclusive gifts'],
    },
    {
      id: 3,
      name: 'Gold',
      level: 3,
      icon: '🥇',
      price: 200000,
      duration: 30,
      is_popular: true,
      benefits: ['Special badge', '+20% coin bonus', 'Priority support', 'Exclusive gifts', 'Custom profile theme'],
    },
    {
      id: 4,
      name: 'Emperor',
      level: 7,
      icon: '👑',
      price: 1000000,
      duration: 30,
      is_popular: false,
      benefits: ['Special badge', '+50% coin bonus', 'VIP support', 'All exclusive features', 'Emperor entrance effect'],
    },
  ];

  giftPacks.value = [
    { id: 1, name: 'Rose Pack', icon: '🌹', quantity: 10, coins: 100 },
    { id: 2, name: 'Heart Pack', icon: '💖', quantity: 10, coins: 500 },
    { id: 3, name: 'Diamond Pack', icon: '💎', quantity: 5, coins: 1000 },
    { id: 4, name: 'Crown Pack', icon: '👑', quantity: 3, coins: 5000 },
  ];

  purchaseHistory.value = [
    { date: new Date(Date.now() - 86400000), item: '10,000 Coins', amount: 170000, status: 'completed' },
    { date: new Date(Date.now() - 172800000), item: 'Gold VIP (30 days)', amount: 200000, status: 'completed' },
  ];
});
</script>
