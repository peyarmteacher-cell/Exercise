<?php
// index.php - หน้าเว็บหลักเวอร์ชัน PHP ล้วน (ไม่ต้อง Build)
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI EduGenerator - ระบบสร้างใบงานอัจฉริยะ</title>
    <!-- Tailwind CSS via CDN (Development) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Sarabun:wght@300;400;500;700&display=swap" rel="stylesheet">
    <!-- Alpine.js -->
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
            <div class="flex items-center gap-2" @click="activeTab = user && user.is_admin ? 'admin' : 'create'" class="cursor-pointer">
                <div class="p-2 bg-indigo-600 rounded-lg text-white">
                    <i data-lucide="graduation-cap" class="w-5 h-5"></i>
                </div>
                <h1 class="font-bold text-xl tracking-tight">AI <span class="text-indigo-600">EduGenerator</span></h1>
            </div>

            <div class="flex items-center gap-4" x-show="user">
                <!-- Teacher Menus -->
                <template x-if="user && user.is_admin === 0">
                    <div class="flex items-center gap-4">
                        <button @click="activeTab = 'create'" :class="activeTab === 'create' ? 'text-indigo-600 font-bold' : 'text-slate-500'" class="text-sm">สร้างใบงาน</button>
                        <button @click="activeTab = 'history'" :class="activeTab === 'history' ? 'text-indigo-600 font-bold' : 'text-slate-500'" class="text-sm">ประวัติ</button>
                        <button @click="activeTab = 'profile'" :class="activeTab === 'profile' ? 'text-indigo-600 font-bold' : 'text-slate-500'" class="text-sm">ข้อมูลส่วนตัว</button>
                    </div>
                </template>

                <!-- Admin Menus -->
                <template x-if="user && user.is_admin === 1">
                    <button @click="activeTab = 'admin'" :class="activeTab === 'admin' ? 'text-indigo-600 font-bold' : 'text-slate-500'" class="text-sm flex items-center gap-1">
                        จัดการรายชื่อครู <span class="bg-red-500 text-white text-[10px] px-1 rounded-full" x-show="pendingCount > 0" x-text="pendingCount"></span>
                    </button>
                </template>

                <div class="h-8 w-px bg-slate-200"></div>
                <div class="flex items-center gap-3">
                    <div class="text-right hidden sm:block">
                        <p class="text-xs font-bold leading-none" x-text="user.fullname"></p>
                        <p class="text-[10px] text-slate-400 mt-1 uppercase" x-text="user.rank"></p>
                    </div>
                    <!-- Tools -->
                    <div class="flex items-center gap-2">
                        <button @click="clearAllData()" class="px-3 py-1 text-[10px] font-bold text-red-500 hover:bg-red-50 rounded-lg border border-red-100 transition-colors flex items-center gap-1" title="ล้างแคชและเริ่มใหม่">
                            <i data-lucide="trash-2" class="w-3 h-3"></i> ล้างแคช
                        </button>
                        <button @click="logout()" class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 hover:bg-slate-200" title="ออกจากระบบ">
                            <i data-lucide="log-out" class="w-4 h-4"></i>
                        </button>
                    </div>
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
                            <span x-text="authMode === 'login' ? 'ยังไม่มีบัญชีสมาชิก?' : 'มีบัญชีอยู่แล้ว?'"></span>
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
            <template x-if="user && user.is_admin === 1 && pendingCount > 0">
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
                
            <?php include 'teacher_tabs.php'; ?>
            <?php include 'admin_tab.php'; ?>
        </div>
    </main>

    <script>
        function app() {
            return {
                user: null,
                activeTab: 'create',
                authMode: 'login',
                authForm: { id_card: '', password: '', fullname: '', rank: 'ครู' },
                profileForm: { fullname: '', rank: '', password: '', gemini_api_key: '' },
                loading: false,
                error: null,
                ranks: ['ครูอัตราจ้าง', 'พนักงานราชการ', 'ครูผู้ช่วย', 'ครู', 'ครูชำนาญการ', 'ครูชำนาญการพิเศษ', 'ครูเชี่ยวชาญ', 'ครูเชี่ยวชาญพิเศษ'],
                config: { topic: '', subject: 'คณิตศาสตร์', level: 'ประถมศึกษาปีที่ 1', difficulty: 'ปานกลาง', types: ['multiple_choice'] },
                exerciseTypes: [
                    { id: 'multiple_choice', name: 'ปรนัย (กขค)' },
                    { id: 'subjective', name: 'อัตนัย (เขียนตอบ)' },
                    { id: 'matching', name: 'จับคู่' },
                    { id: 'fill_in_the_blanks', name: 'เติมคำในช่องว่าง' },
                    { id: 'math_show_work', name: 'คณิตศาสตร์ (แสดงวิธีทำ)' },
                    { id: 'analysis_reasoning', name: 'วิเคราะห์/แสดงเหตุผล' }
                ],
                currentSet: null,
                history: [],
                pendingUsers: [],
                allUsers: [],
                adminView: 'pending',
                pendingCount: 0,

                async init() {
                    const saved = localStorage.getItem('schoolos_user');
                    if (saved) {
                        try {
                            this.user = JSON.parse(saved);
                            this.user.is_admin = Number(this.user.is_admin);
                            this.syncProfileForm();
                            
                            if (this.user.is_admin === 1) {
                                this.activeTab = 'admin';
                                this.fetchAdminData();
                                setInterval(() => this.fetchAdminData(), 30000);
                            } else {
                                this.activeTab = 'create';
                                this.fetchHistory();
                            }
                        } catch (e) {
                            localStorage.removeItem('schoolos_user');
                        }
                    }
                    this.$nextTick(() => lucide.createIcons());
                },

                syncProfileForm() {
                    if (this.user) {
                        this.profileForm = {
                            fullname: this.user.fullname,
                            rank: this.user.rank,
                            password: '',
                            gemini_api_key: this.user.gemini_api_key || ''
                        };
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
                            this.user.is_admin = Number(this.user.is_admin);
                            this.syncProfileForm();
                            localStorage.setItem('schoolos_user', JSON.stringify(data));
                            
                            if (this.user.is_admin === 1) {
                                this.activeTab = 'admin';
                                this.fetchAdminData();
                            } else {
                                this.activeTab = 'create';
                                this.fetchHistory();
                            }
                        } else { this.error = data.error; }
                    } finally { 
                        this.loading = false;
                        this.$nextTick(() => lucide.createIcons());
                    }
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
                    this.user = null; 
                    localStorage.removeItem('schoolos_user');
                    this.authMode = 'login';
                    this.activeTab = 'create';
                },

                async updateProfile() {
                    this.loading = true;
                    try {
                        const payload = { ...this.profileForm, id: this.user.id };
                        const res = await fetch('api.php?path=user/update-profile', {
                            method: 'POST',
                            body: JSON.stringify(payload)
                        });
                        const data = await res.json();
                        if (res.ok) {
                            this.user = data;
                            this.user.is_admin = Number(this.user.is_admin);
                            localStorage.setItem('schoolos_user', JSON.stringify(data));
                            alert('อัปเดตข้อมูลสำเร็จ');
                            this.syncProfileForm();
                        } else { alert('เกิดข้อผิดพลาด: ' + (data.error || 'ไม่สามารถบันทึกข้อมูลได้')); }
                    } catch (e) { alert('ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้'); }
                    finally { this.loading = false; }
                },

                async generateAI() {
                    if (!this.config.topic) return alert('กรุณาระบุหัวข้อ');
                    const apiKey = this.user.gemini_api_key;
                    if (!apiKey) return alert('กรุณาระบุ Gemini API Key ในหน้าข้อมูลส่วนตัวก่อนใช้งาน');
                    
                    this.loading = true;
                    try {
                        const typesStr = this.config.types.join(", ");
                        const fullPrompt = `คุณคือผู้เชี่ยวชาญการศึกษาไทย 
                        ภารกิจ: สร้างแบบฝึกหัดเรื่อง '${this.config.topic}' ชั้น ${this.config.level} วิชา ${this.config.subject}
                        รูปแบบ: ${typesStr} | จำนวน: 10 ข้อ
                        
                        ให้ตอบกลับเป็น JSON ภาษาไทยที่มีโครงสร้างดังนี้เท่านั้น (ห้ามมีข้อความอื่นนอกเหนือจาก JSON):
                        {
                          "title": "หัวข้อ",
                          "description": "คำชี้แจง",
                          "indicators": "ตัวชี้วัด",
                          "questions": [
                            {
                              "type": "ประเภทของข้อสอบ",
                              "question": "โจทย์",
                              "options": [{"id":"a", "text":"ตัวเลือก"}],
                              "answer": "เฉลย",
                              "explanation": "คำอธิบาย"
                            }
                          ]
                        }`;

                        // ใช้ VERSION v1 (Stable) เพื่อความแน่นอนสูงสุด
                        const url = `https://generativelanguage.googleapis.com/v1/models/gemini-1.5-flash:generateContent?key=${apiKey}`;
                        const response = await fetch(url, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({
                                contents: [{ parts: [{ text: fullPrompt }] }]
                            })
                        });

                        const resData = await response.json();
                        if (!response.ok) throw new Error(resData.error?.message || 'AI API Error');

                        let aiText = resData.candidates[0].content.parts[0].text;
                        // ลบ markdown wrapper ถ้ามี
                        aiText = aiText.replace(/```json/g, '').replace(/```/g, '').trim();
                        
                        this.currentSet = JSON.parse(aiText);
                        this.currentSet.subject = this.config.subject;
                        this.currentSet.level = this.config.level;
                        this.$nextTick(() => lucide.createIcons());
                        
                    } catch (e) {
                        alert('เกิดข้อผิดพลาด: ' + e.message);
                    } finally {
                        this.loading = false;
                    }
                },

                clearAllData() {
                    if(confirm('ยืนยันล้างข้อมูลแคชและออกจากระบบ?\n(วิธีนี้จะช่วยแก้ปัญหาหากระบบจำคีย์เก่าตัวที่เสียอยู่)')) {
                        localStorage.clear();
                        window.location.reload();
                    }
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
                    this.$nextTick(() => lucide.createIcons());
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
                },

                async updateDatabase() {
                    if (!confirm('คุณต้องการปรับปรุงโครงสร้างฐานข้อมูลใช่หรือไม่?')) return;
                    this.loading = true;
                    try {
                        const res = await fetch('api.php?path=admin/migrate', { method: 'POST' });
                        const data = await res.json();
                        alert(data.message);
                        await this.fetchAdminData();
                    } catch (e) { alert('เกิดข้อผิดพลาดในการปรับปรุงฐานข้อมูล'); }
                    finally { this.loading = false; }
                }
            }
        }
    </script>
</body>
</html>
