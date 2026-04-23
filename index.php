<?php
// index.php - หน้าเว็บหลักเวอร์ชัน PHP ล้วน (ไม่ต้อง Build)
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI EduGenerator - ระบบสร้างใบงานอัจฉริยะ</title>
    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Sarabun:wght@300;400;500;700&display=swap" rel="stylesheet">
    <!-- Alpine.js (เพื่อความลื่นไหลโดยไม่ต้อง Build) -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <style>
        body { font-family: 'Inter', 'Sarabun', sans-serif; }
        [x-cloak] { display: none !important; }
        @media print {
            .no-print { display: none !important; }
            .print-area { padding: 0 !important; border: none !important; box-shadow: none !important; }
        }
        .shadow-polish { box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 min-h-screen flex flex-col" x-data="app()" x-init="init()">

    <!-- Header / Navigation -->
    <nav class="bg-white border-b border-slate-200 px-6 py-3 sticky top-0 z-50 no-print shadow-sm">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="p-2 bg-indigo-600 rounded-lg text-white">
                    <i data-lucide="graduation-cap" class="w-5 h-5"></i>
                </div>
                <h1 class="font-bold text-xl tracking-tight">AI <span class="text-indigo-600">EduGenerator</span></h1>
            </div>

            <div class="flex items-center gap-4" x-show="user">
                <button @click="activeTab = 'create'" :class="activeTab === 'create' ? 'text-indigo-600 font-bold' : 'text-slate-500'" class="text-sm">สร้างใบงาน</button>
                <button @click="activeTab = 'history'" :class="activeTab === 'history' ? 'text-indigo-600 font-bold' : 'text-slate-500'" class="text-sm">ประวัติ</button>
                <template x-if="user && user.is_admin">
                    <button @click="activeTab = 'admin'" :class="activeTab === 'admin' ? 'text-indigo-600 font-bold' : 'text-slate-500'" class="text-sm flex items-center gap-1">
                        จัดการครู <span class="bg-red-500 text-white text-[10px] px-1 rounded-full" x-show="pendingCount > 0" x-text="pendingCount"></span>
                    </button>
                </template>
                <div class="h-8 w-px bg-slate-200"></div>
                <div class="flex items-center gap-3">
                    <div class="text-right hidden sm:block">
                        <p class="text-xs font-bold leading-none" x-text="user.fullname"></p>
                        <p class="text-[10px] text-slate-400 mt-1 uppercase" x-text="user.rank"></p>
                    </div>
                    <button @click="logout()" class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 hover:bg-slate-200">
                        <i data-lucide="log-out" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-1 p-6 max-w-7xl mx-auto w-full">
        
        <!-- Login / Register Screen -->
        <div x-show="!user" x-cloak class="max-w-md mx-auto py-12">
            <div class="bg-white rounded-2xl shadow-xl border border-slate-200 p-8">
                <div class="text-center mb-8">
                    <h2 class="text-2xl font-bold text-slate-800" x-text="authMode === 'login' ? 'เข้าสู่ระบบ' : 'ลงทะเบียนคุณครูใหม่'"></h2>
                    <p class="text-slate-500 text-sm mt-2" x-text="authMode === 'login' ? 'กรุณากรอกเลขบัตรประชาชนเพื่อเข้าใช้งาน' : 'กรอกข้อมูลให้ครบถ้วนเพื่อขออนุมัติใช้งาน'"></p>
                </div>

                <form @submit.prevent="authMode === 'login' ? login() : register()" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1 uppercase tracking-wider">หมายเลขบัตรประชาชน (Username)</label>
                        <input type="text" x-model="authForm.id_card" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none" required>
                    </div>
                    
                    <div x-show="authMode === 'register'" x-cloak class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1 uppercase tracking-wider">ชื่อ-นามสกุล</label>
                            <input type="text" x-model="authForm.fullname" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1 uppercase tracking-wider">ตำแหน่ง</label>
                            <select x-model="authForm.rank" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                                <template x-for="r in ranks">
                                    <option :value="r" x-text="r"></option>
                                </template>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1 uppercase tracking-wider">รหัสผ่าน</label>
                        <input type="password" x-model="authForm.password" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none" required>
                    </div>

                    <button type="submit" class="w-full bg-indigo-600 text-white font-bold py-3 rounded-xl shadow-lg hover:bg-indigo-700 transition-all flex items-center justify-center gap-2" :disabled="loading">
                        <span x-show="loading" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                        <span x-text="authMode === 'login' ? 'เข้าสู่ระบบ' : 'ส่งข้อมูลเพื่อขออนุมัติ'"></span>
                    </button>
                    
                    <div class="text-center mt-6">
                        <p class="text-sm text-slate-500">
                            <span x-text="authMode === 'login' ? 'ยังไม่มีบัญชีสมาชิิก?' : 'มีบัญชีอยู่แล้ว?'"></span>
                            <button type="button" @click="authMode = authMode === 'login' ? 'register' : 'login'; error = null" class="text-indigo-600 font-bold ml-1 hover:underline">
                                <span x-text="authMode === 'login' ? 'สมัครสมาชิกที่นี่' : 'เข้าสู่ระบบที่นี่'"></span>
                            </button>
                        </p>
                    </div>
                </form>

                <div x-show="error" x-cloak class="mt-4 p-3 bg-red-50 text-red-600 rounded-xl text-xs border border-red-100" x-text="error"></div>
            </div>
        </div>

    <!-- Dashboard / Main Interface -->
    <div x-show="user" x-cloak class="h-full">
        
        <!-- Admin Notification Banner (Visible to Super Admin only) -->
        <template x-if="user && user.is_admin && pendingCount > 0">
            <div class="mb-6 bg-amber-50 border border-amber-200 rounded-2xl p-4 flex items-center justify-between animate-pulse">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-amber-500 text-white rounded-lg">
                        <i data-lucide="user-plus" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-amber-900 text-sm">มีการขออนุมัติการใช้งานใหม่</h4>
                        <p class="text-amber-700 text-xs" x-text="'มีคุณครูจำนวน ' + pendingCount + ' ท่านรอการตรวจสอบและอนุมัติจากคุณ'"></p>
                    </div>
                </div>
                <button @click="activeTab = 'admin'; adminView = 'pending'" class="bg-amber-100 hover:bg-amber-200 text-amber-700 px-4 py-2 rounded-xl text-xs font-bold transition-colors">
                    ไปที่หน้าจัดการ
                </button>
            </div>
        </template>
            
        <!-- Create Tab -->
            <div x-show="activeTab === 'create'" class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                <!-- Sidebar Settings -->
                <aside class="lg:col-span-3 space-y-6 no-print">
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
                        <h3 class="font-bold text-slate-800 mb-4 flex items-center gap-2">
                            <i data-lucide="sparkles" class="w-4 h-4 text-indigo-500"></i> AI Settings
                        </h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">หัวข้อเรื่อง</label>
                                <textarea x-model="config.topic" class="w-full bg-slate-50 border border-slate-200 rounded-lg p-3 text-sm h-24 outline-none focus:ring-1 focus:ring-indigo-500" placeholder="เช่น ระบบสุริยะ..."></textarea>
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">วิชา</label>
                                <select x-model="config.subject" class="w-full bg-slate-50 border border-slate-200 rounded-lg p-2 text-sm outline-none">
                                    <option>คณิตศาสตร์</option>
                                    <option>ภาษาไทย</option>
                                    <option>วิทยาศาสตร์</option>
                                </select>
                            </div>
                            <button @click="generateAI()" class="w-full bg-indigo-600 text-white font-bold py-3 rounded-xl shadow-indigo-100 shadow-lg hover:shadow-indigo-200 transition-all flex items-center justify-center gap-2" :disabled="loading">
                                <span x-show="loading" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                                <span x-text="loading ? 'กำลังประมวลผล...' : '⚡ สร้างใบงานด้วย AI'"></span>
                            </button>
                        </div>
                    </div>
                </aside>

                <!-- Preview Area -->
                <div class="lg:col-span-9 bg-white min-h-[800px] rounded-2xl shadow-sm border border-slate-200 p-8 print-area relative">
                    <template x-if="!currentSet">
                        <div class="h-full flex flex-col items-center justify-center text-slate-300 py-40">
                            <i data-lucide="file-text" class="w-16 h-16 mb-4"></i>
                            <p class="font-medium text-lg">ยังไม่มีใบงาน กรุณาใช้ AI Generate ด้านซ้ายมือ</p>
                        </div>
                    </template>

                    <template x-if="currentSet">
                        <div>
                            <!-- Header -->
                            <div class="border-b-2 border-slate-900 pb-4 mb-8 flex items-start justify-between">
                                <div>
                                    <h1 class="text-2xl font-bold text-slate-900" x-text="currentSet.title"></h1>
                                    <p class="text-sm text-slate-600 mt-1">วิชา: <span x-text="currentSet.subject"></span> | ระดับชั้น: <span x-text="currentSet.level"></span></p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-bold">ชื่อ-นามสกุล: .................................................................</p>
                                    <p class="text-sm font-bold mt-2">เลขที่: ............ ชั้น: ............ วันที่: ..............................</p>
                                </div>
                            </div>

                            <!-- Questions -->
                            <div class="space-y-6">
                                <template x-for="(q, idx) in currentSet.questions" :key="idx">
                                    <div class="border-b border-slate-100 pb-6">
                                        <div class="flex gap-2">
                                            <span class="font-bold" x-text="(idx+1) + '.'"></span>
                                            <p class="font-medium flex-1 text-slate-800" x-text="q.question"></p>
                                        </div>

                                        <!-- Multiple Choice -->
                                        <template x-if="q.type === 'multiple_choice'">
                                            <div class="grid grid-cols-2 gap-2 mt-3 ml-6">
                                                <template x-for="opt in q.options" :key="opt.id">
                                                    <div class="flex items-center gap-2">
                                                        <div class="w-4 h-4 border border-slate-400 rounded-full"></div>
                                                        <span class="text-sm" x-text="opt.text"></span>
                                                    </div>
                                                </template>
                                            </div>
                                        </template>

                                        <!-- Matching -->
                                        <template x-if="q.type === 'matching'">
                                            <div class="mt-3 ml-6 space-y-2">
                                                <template x-for="pair in q.pairs">
                                                    <div class="flex items-center gap-4">
                                                        <div class="w-32 py-1 px-2 border-b border-slate-300 text-sm italic">........................</div>
                                                        <div class="text-sm text-slate-600">คู่กับ</div>
                                                        <div class="flex-1 bg-slate-50 p-2 border border-slate-100 rounded text-sm" x-text="pair.right"></div>
                                                    </div>
                                                </template>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>

                    <!-- Floating Action Buttons -->
                    <div class="fixed bottom-10 right-10 flex flex-col gap-3 no-print" x-show="currentSet">
                        <button @click="saveSet()" class="p-4 bg-indigo-600 text-white rounded-full shadow-2xl hover:scale-105 transition-all">
                            <i data-lucide="save" class="w-6 h-6"></i>
                        </button>
                        <button @click="window.print()" class="p-4 bg-slate-800 text-white rounded-full shadow-2xl hover:scale-105 transition-all">
                            <i data-lucide="printer" class="w-6 h-6"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- History Tab -->
            <div x-show="activeTab === 'history'" class="space-y-6">
                <h2 class="text-xl font-bold flex items-center gap-2"><i data-lucide="history" class="w-5 h-5"></i> ประวัติการสร้างใบงาน</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <template x-for="item in history" :key="item.id">
                        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition-all group">
                            <div class="flex items-start justify-between mb-4">
                                <div class="p-3 bg-indigo-50 text-indigo-600 rounded-xl"><i data-lucide="file-text" class="w-6 h-6"></i></div>
                                <button @click="deleteExercise(item.id)" class="text-slate-300 hover:text-red-500 transition-colors"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                            </div>
                            <h3 class="font-bold text-slate-800 line-clamp-2" x-text="item.title"></h3>
                            <p class="text-xs text-slate-400 mt-2" x-text="'วันที่: ' + new Date(item.created_at).toLocaleDateString('th-TH')"></p>
                            <button @click="currentSet = item; activeTab = 'create'" class="w-full mt-4 py-2 border border-indigo-100 text-indigo-600 rounded-lg font-bold text-xs hover:bg-indigo-50 transition-colors">เปิดดูรายการนี้</button>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Admin Tab -->
            <div x-show="activeTab === 'admin' && user && user.is_admin" class="space-y-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-bold flex items-center gap-2"><i data-lucide="users" class="w-5 h-5"></i> ระบบจัดการสมาชิก</h2>
                    <div class="flex bg-slate-200 p-1 rounded-lg">
                        <button @click="adminView = 'pending'" :class="adminView === 'pending' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-500'" class="px-4 py-1.5 rounded-md text-xs font-bold">รอการอนุมัติ</button>
                        <button @click="adminView = 'all'" :class="adminView === 'all' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-500'" class="px-4 py-1.5 rounded-md text-xs font-bold">สมาชิกทั้งหมด</button>
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
                    <template x-if="adminView === 'pending'">
                        <div class="divide-y divide-slate-100">
                            <template x-if="pendingUsers.length === 0">
                                <div class="py-20 text-center text-slate-300">ไม่มีคำขอที่รอการอนุมัติ</div>
                            </template>
                            <template x-for="p in pendingUsers" :key="p.id">
                                <div class="p-6 flex items-center justify-between">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center font-bold" x-text="p.fullname.charAt(0)"></div>
                                        <div>
                                            <p class="font-bold text-sm" x-text="p.fullname"></p>
                                            <p class="text-xs text-slate-500" x-text="p.rank + ' | ID: ' + p.id_card"></p>
                                        </div>
                                    </div>
                                    <div class="flex gap-2">
                                        <button @click="updateStatus(p.id, 'approved')" class="bg-indigo-600 text-white px-4 py-1.5 rounded-lg text-xs font-bold">อนุมัติ</button>
                                        <button @click="deleteUser(p.id)" class="text-red-500 px-4 py-1.5 rounded-lg text-xs font-bold hover:bg-red-50">ลบออก</button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>

                    <template x-if="adminView === 'all'">
                        <div class="divide-y divide-slate-100">
                            <template x-for="p in allUsers" :key="p.id">
                                <div class="p-6 hover:bg-slate-50 transition-colors">
                                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                                        <div class="flex items-center gap-4">
                                            <div class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-full flex items-center justify-center font-bold text-lg" x-text="p.fullname.charAt(0)"></div>
                                            <div>
                                                <div class="flex items-center gap-2">
                                                    <p class="font-bold text-slate-800" x-text="p.fullname"></p>
                                                    <span :class="p.status === 'approved' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'" 
                                                          class="text-[9px] px-2 py-0.5 rounded-full font-bold uppercase" x-text="p.status"></span>
                                                </div>
                                                <p class="text-xs text-slate-500" x-text="p.rank + ' | ID: ' + p.id_card"></p>
                                            </div>
                                        </div>
                                        
                                        <div class="flex flex-wrap gap-4 items-center">
                                            <!-- Stats Grid -->
                                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 bg-slate-50 p-3 rounded-xl border border-slate-100">
                                                <div class="text-center px-2">
                                                    <p class="text-[9px] uppercase font-bold text-slate-400">เป็นสมาชิกมาแล้ว</p>
                                                    <p class="text-sm font-bold text-indigo-600" x-text="p.membership_days + ' วัน'"></p>
                                                </div>
                                                <div class="text-center px-2 border-l border-slate-200">
                                                    <p class="text-[9px] uppercase font-bold text-slate-400">ใบงานทั้งหมด</p>
                                                    <p class="text-sm font-bold text-slate-700" x-text="p.exercise_count + ' เรื่อง'"></p>
                                                </div>
                                                <div class="text-center px-2 border-l border-slate-200">
                                                    <p class="text-[9px] uppercase font-bold text-slate-400">เข้าใช้งาน</p>
                                                    <p class="text-sm font-bold text-slate-700" x-text="p.login_count + ' ครั้ง'"></p>
                                                </div>
                                                <div class="text-center px-2 border-l border-slate-200">
                                                    <p class="text-[9px] uppercase font-bold text-slate-400">ล่าสุดเมื่อ</p>
                                                    <p class="text-[10px] font-bold text-slate-500" x-text="p.last_login ? new Date(p.last_login).toLocaleDateString('th-TH') : '-'"></p>
                                                </div>
                                            </div>

                                            <div class="flex gap-2">
                                                <button @click="deleteUser(p.id)" class="p-2 text-slate-300 hover:text-red-500 transition-colors">
                                                    <i data-lucide="trash-2" class="w-5 h-5"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                            <template x-if="allUsers.length === 0">
                                <div class="py-20 text-center text-slate-300">ยังไม่มีสมาชิกในระบบ</div>
                            </template>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </main>

    <script>
        function app() {
            return {
                user: null,
                activeTab: 'create',
                authMode: 'login',
                authForm: { id_card: '', password: '', fullname: '', rank: 'ครู' },
                loading: false,
                error: null,
                ranks: ['ครูอัตราจ้าง', 'พนักงานราชการ', 'ครูผู้ช่วย', 'ครู', 'ครูชำนาญการ', 'ครูชำนาญการพิเศษ', 'ครูเชี่ยวชาญ', 'ครูเชี่ยวชาญพิเศษ'],
                config: { topic: '', subject: 'คณิตศาสตร์', level: 'ประถมศึกษาปีที่ 1' },
                currentSet: null,
                history: [],
                pendingUsers: [],
                allUsers: [],
                adminView: 'pending',
                pendingCount: 0,

                async init() {
                    lucide.createIcons();
                    const saved = localStorage.getItem('schoolos_user');
                    if (saved) {
                        this.user = JSON.parse(saved);
                        this.fetchHistory();
                        if (this.user.is_admin) {
                            this.fetchAdminData();
                            // ตรวจสอบสมาชิกใหม่ทุกๆ 30 วินาที
                            setInterval(() => this.fetchAdminData(), 30000);
                        }
                    }
                },

                async login() {
                    this.loading = true; this.error = null;
                    try {
                        const res = await fetch('api.php?path=login', {
                            method: 'POST',
                            body: JSON.stringify(this.authForm)
                        });
                        const data = await res.json();
                        if (res.ok) {
                            this.user = data;
                            localStorage.setItem('schoolos_user', JSON.stringify(data));
                            this.fetchHistory();
                        } else { this.error = data.error; }
                    } finally { this.loading = false; }
                },

                async register() {
                    this.loading = true; this.error = null;
                    try {
                        const res = await fetch('api.php?path=register', {
                            method: 'POST',
                            body: JSON.stringify(this.authForm)
                        });
                        const data = await res.json();
                        if (res.ok) { alert(data.message); this.authMode = 'login'; }
                        else { this.error = data.error; }
                    } finally { this.loading = false; }
                },

                logout() {
                    this.user = null; localStorage.removeItem('schoolos_user');
                },

                async generateAI() {
                    if (!this.config.topic) return alert('กรุณาระบุหัวข้อ');
                    this.loading = true;
                    try {
                        const res = await fetch('api.php?path=generate', {
                            method: 'POST',
                            body: JSON.stringify(this.config)
                        });
                        const data = await res.json();
                        this.currentSet = data;
                        setTimeout(() => lucide.createIcons(), 100);
                    } catch (e) { alert('AI ไม่สามารถสร้างข้อมูลได้ในขณะนี้'); }
                    finally { this.loading = false; }
                },

                async saveSet() {
                    const res = await fetch('api.php?path=exercises', {
                        method: 'POST',
                        body: JSON.stringify({
                            user_id: this.user.id,
                            title: this.currentSet.title,
                            content: this.currentSet,
                            teacher_name: this.user.fullname
                        })
                    });
                    if (res.ok) { alert('บันทึกเรียบร้อย'); this.fetchHistory(); }
                },

                async fetchHistory() {
                    const res = await fetch('api.php?path=exercises&userId=' + this.user.id);
                    this.history = await res.json();
                    setTimeout(() => lucide.createIcons(), 100);
                },

                async fetchAdminData() {
                    const pRes = await fetch('api.php?path=admin/pending-users');
                    this.pendingUsers = await pRes.json();
                    this.pendingCount = this.pendingUsers.length;
                    const aRes = await fetch('api.php?path=admin/users');
                    this.allUsers = await aRes.json();
                    setTimeout(() => lucide.createIcons(), 100);
                },

                async updateStatus(id, status) {
                    await fetch('api.php?path=admin/update-user-status', {
                        method: 'POST',
                        body: JSON.stringify({ id, status })
                    });
                    this.fetchAdminData();
                },

                async deleteUser(id) {
                    if (!confirm('ยืนยันลบสมาชิกท่านนี้?')) return;
                    await fetch('api.php?path=admin/users/' + id, { method: 'DELETE' });
                    this.fetchAdminData();
                },

                async deleteExercise(id) {
                    if (!confirm('ยืนยันลบใบงานนี้?')) return;
                    await fetch('api.php?path=exercises/' + id, { method: 'DELETE' });
                    this.fetchHistory();
                }
            }
        }
    </script>
</body>
</html>
