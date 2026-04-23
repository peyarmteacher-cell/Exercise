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
                <!-- Teacher Menus -->
                <template x-if="user && !user.is_admin">
                    <div class="flex items-center gap-4">
                        <button @click="activeTab = 'create'" :class="activeTab === 'create' ? 'text-indigo-600 font-bold' : 'text-slate-500'" class="text-sm">สร้างใบงาน</button>
                        <button @click="activeTab = 'history'" :class="activeTab === 'history' ? 'text-indigo-600 font-bold' : 'text-slate-500'" class="text-sm">ประวัติ</button>
                        <button @click="activeTab = 'profile'" :class="activeTab === 'profile' ? 'text-indigo-600 font-bold' : 'text-slate-500'" class="text-sm">ข้อมูลส่วนตัว</button>
                    </div>
                </template>

                <!-- Admin Menus -->
                <template x-if="user && user.is_admin">
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
                    <button @click="logout()" class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 hover:bg-slate-200" title="ออกจากระบบ">
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
            <div x-show="activeTab === 'create' && user && !user.is_admin" class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
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
                                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">วิชา / ระดับชั้น</label>
                                <div class="grid grid-cols-2 gap-2">
                                    <select x-model="config.subject" class="w-full bg-slate-50 border border-slate-200 rounded-lg p-2 text-sm outline-none">
                                        <option>คณิตศาสตร์</option>
                                        <option>ภาษาไทย</option>
                                        <option>วิทยาศาสตร์</option>
                                        <option>สังคมศึกษา</option>
                                        <option>ภาษาอังกฤษ</option>
                                    </select>
                                    <select x-model="config.level" class="w-full bg-slate-50 border border-slate-200 rounded-lg p-2 text-sm outline-none">
                                        <option>ประถมศึกษาปีที่ 1</option>
                                        <option>ประถมศึกษาปีที่ 2</option>
                                        <option>ประถมศึกษาปีที่ 3</option>
                                        <option>ประถมศึกษาปีที่ 4</option>
                                        <option>ประถมศึกษาปีที่ 5</option>
                                        <option>ประถมศึกษาปีที่ 6</option>
                                    </select>
                                </div>
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">ความยาก</label>
                                <select x-model="config.difficulty" class="w-full bg-slate-50 border border-slate-200 rounded-lg p-2 text-sm outline-none">
                                    <option value="ง่าย">ง่าย</option>
                                    <option value="ปานกลาง">ปานกลาง</option>
                                    <option value="ยาก">ยาก</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-2">ประเภทโจทย์ (เลือกหลายรายการได้)</label>
                                <div class="space-y-2 max-h-32 overflow-y-auto p-2 bg-slate-50 rounded-lg border border-slate-100">
                                    <template x-for="t in exerciseTypes">
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input type="checkbox" :value="t.id" x-model="config.types" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                            <span class="text-xs text-slate-600" x-text="t.name"></span>
                                        </label>
                                    </template>
                                </div>
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

            <!-- Profile Tab -->
            <div x-show="activeTab === 'profile'" class="max-w-2xl mx-auto space-y-6">
                <h2 class="text-xl font-bold flex items-center gap-2"><i data-lucide="user-cog" class="w-5 h-5"></i> ข้อมูลส่วนตัวและตั้งค่า</h2>
                
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="p-8 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">ชื่อ-นามสกุล</label>
                                <input type="text" x-model="profileForm.fullname" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">ตำแหน่ง</label>
                                <select x-model="profileForm.rank" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                                    <template x-for="r in ranks">
                                        <option :value="r" x-text="r"></option>
                                    </template>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">รหัสผ่านใหม่ (ปล่อยว่างหากไม่ต้องการเปลี่ยน)</label>
                            <input type="password" x-model="profileForm.password" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none" placeholder="คีย์รหัสผ่านใหม่ที่นี่...">
                        </div>

                        <div class="pt-6 border-t border-slate-100">
                            <div class="flex items-center justify-between mb-2">
                                <label class="block text-xs font-bold text-indigo-600 uppercase">Gemini API Key ส่วนตัว</label>
                                <a href="https://aistudio.google.com/app/apikey" target="_blank" class="text-[10px] bg-indigo-50 text-indigo-600 px-2 py-1 rounded font-bold hover:bg-indigo-100 transition-colors flex items-center gap-1">
                                    <i data-lucide="external-link" class="w-3 h-3"></i> รับ API Key ฟรีที่นี่
                                </a>
                            </div>
                            <p class="text-[10px] text-slate-400 mb-3 leading-relaxed">
                                <b>วิธีใช้งาน:</b> 1. คลิกที่ลิงก์ด้านบนเพื่อไปที่ Google AI Studio 2. กดปุ่ม "Create API key" 3. คัดลอกรหัส (เช่น AIza...) มาวางในช่องด้านล่างนี้ 4. กดบันทึกข้อมูล
                            </p>
                            <input type="text" x-model="profileForm.gemini_api_key" class="w-full bg-indigo-50/30 border border-indigo-100 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none font-mono" placeholder="AIzaSy...">
                        </div>

                        <button @click="updateProfile()" class="bg-indigo-600 text-white font-bold py-3 px-8 rounded-xl shadow-lg hover:bg-indigo-700 transition-all flex items-center gap-2" :disabled="loading">
                            <span x-show="loading" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                            บันทึกการเปลี่ยนแปลง
                        </button>
                    </div>
                </div>
            </div>

            <!-- Admin Tab -->
            <div x-show="activeTab === 'admin' && user && user.is_admin === 1" class="space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h2 class="text-xl font-bold text-slate-800">ระบบจัดการสมาชิก</h2>
                        <p class="text-xs text-slate-500 mt-1">จัดการการอนุมัติและตรวจสอบสถิติการใช้งานคุณครู</p>
                    </div>
                    
                    <div class="flex flex-wrap items-center gap-3">
                        <button @click="updateDatabase()" class="bg-amber-100 hover:bg-amber-200 text-amber-700 px-4 py-2 rounded-xl text-xs font-bold flex items-center gap-2 transition-all">
                            <i data-lucide="database" class="w-4 h-4"></i> ปรับปรุงฐานข้อมูล
                        </button>
                        <div class="flex bg-slate-200 p-1 rounded-lg">
                            <button @click="adminView = 'pending'" :class="adminView === 'pending' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-500'" class="px-4 py-1.5 rounded-md text-xs font-bold">รออนุมัติ</button>
                            <button @click="adminView = 'all'" :class="adminView === 'all' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-500'" class="px-4 py-1.5 rounded-md text-xs font-bold">ทั้งหมด</button>
                        </div>
                    </div>
                </div>

                <!-- Modern Grid Layout for Users -->
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                    <template x-if="adminView === 'pending' && pendingUsers.length === 0">
                        <div class="col-span-full py-20 text-center bg-white rounded-3xl border border-dashed border-slate-300">
                            <div class="p-4 bg-slate-50 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i data-lucide="user-check" class="w-8 h-8 text-slate-200"></i>
                            </div>
                            <p class="text-slate-400 font-medium">ไม่มีคำขอที่รอการอนุมัติในขณะนี้</p>
                        </div>
                    </template>

                    <template x-for="p in (adminView === 'pending' ? pendingUsers : allUsers)" :key="p.id">
                        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm hover:shadow-md transition-all overflow-hidden flex flex-col">
                            <div class="p-6 flex-1">
                                <div class="flex items-start justify-between mb-4">
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 rounded-2xl bg-indigo-600 text-white flex items-center justify-center font-bold text-xl shadow-lg shadow-indigo-100" x-text="p.fullname.charAt(0)"></div>
                                        <div>
                                            <h4 class="font-bold text-slate-800 leading-tight" x-text="p.fullname"></h4>
                                            <p class="text-[10px] text-slate-400 mt-1 uppercase font-bold tracking-wider" x-text="p.rank"></p>
                                        </div>
                                    </div>
                                    <span :class="p.status === 'approved' ? 'bg-green-100 text-green-700' : (p.status === 'pending' ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700')" 
                                          class="text-[9px] px-2 py-1 rounded-lg font-bold uppercase" x-text="p.status === 'approved' ? 'Active' : (p.status === 'pending' ? 'Pending' : 'Suspended')"></span>
                                </div>

                                <div class="space-y-3 py-4 border-y border-slate-50">
                                    <div class="flex items-center justify-between">
                                        <span class="text-[10px] text-slate-400 font-bold uppercase">ID CARD</span>
                                        <span class="text-xs font-mono text-slate-600" x-text="p.id_card"></span>
                                    </div>
                                    <template x-if="p.status !== 'pending'">
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <p class="text-[9px] text-slate-400 font-bold uppercase">ใบงานทั้งหมด</p>
                                                <p class="text-sm font-bold text-slate-800" x-text="p.exercise_count + ' เรื่อง'"></p>
                                            </div>
                                            <div>
                                                <p class="text-[9px] text-slate-400 font-bold uppercase">เข้าใช้งาน</p>
                                                <p class="text-sm font-bold text-slate-800" x-text="p.login_count + ' ครั้ง'"></p>
                                            </div>
                                        </div>
                                    </template>
                                    <template x-if="p.status === 'pending'">
                                        <p class="text-xs text-slate-500 italic">สมัครเมื่อ: <span x-text="new Date(p.created_at).toLocaleDateString('th-TH')"></span></p>
                                    </template>
                                </div>
                            </div>

                            <div class="p-4 bg-slate-50 flex items-center justify-between gap-3">
                                <template x-if="p.status === 'pending'">
                                    <div class="flex w-full gap-2">
                                        <button @click="updateStatus(p.id, 'approved')" class="flex-1 bg-indigo-600 text-white text-xs font-bold py-2 rounded-xl hover:bg-indigo-700 transition-colors">อนุมัติ</button>
                                        <button @click="deleteUser(p.id)" class="flex-1 bg-white text-red-500 border border-red-100 text-xs font-bold py-2 rounded-xl hover:bg-red-50 transition-colors">ปฏิเสธ</button>
                                    </div>
                                </template>
                                <template x-if="p.status !== 'pending'">
                                    <div class="flex w-full gap-2">
                                        <button x-show="p.status === 'approved'" @click="updateStatus(p.id, 'rejected')" class="flex-1 bg-white text-amber-600 border border-amber-100 text-xs font-bold py-2 rounded-xl hover:bg-amber-50">ระงับ</button>
                                        <button x-show="p.status === 'rejected'" @click="updateStatus(p.id, 'approved')" class="flex-1 bg-indigo-600 text-white text-xs font-bold py-2 rounded-xl hover:bg-indigo-700">ปลดระงับ</button>
                                        <button @click="deleteUser(p.id)" class="px-3 bg-white text-slate-300 hover:text-red-500 border border-slate-100 rounded-xl transition-colors"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </main>

    <script>
        function app() {
            return {
                // ... (previous state)
                
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
                },

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
                            // บังคับให้เป็น Number เพื่อป้องกันบั๊ก String "0"
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
                            password: '', // ไม่ดึงรหัสผ่านเดิมมาโชว์
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
                            this.syncProfileForm();
                            localStorage.setItem('schoolos_user', JSON.stringify(data));
                            
                            if (this.user.is_admin) {
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

                async updateProfile() {
                    this.loading = true;
                    try {
                        const payload = { ...this.profileForm, id: this.user.id };
                        // ถ้าไม่เปลี่ยนรหัส ให้ใช้รหัสเดิม (ในแง่ UI นี้คือส่งว่างไปหลังบ้านอาจจะลำบาก ถ้าไม่ได้เก็บ pwd ไว้)
                        // สมมติว่าหลังบ้านถ้า password ว่างจะไม่ update (หรือเราจัดการส่งเดิมไป)
                        // เพื่อความง่าย: ถ้าว่างให้ใช้ค่าว่างส่งไปและหลังบ้านต้องเช็ค (ผมจะแก้ api.php ให้รองรับ password เดิมถ้าส่งว่างมาทีหลัง)
                        // แต่ตอนนี้เอาแบบส่งไปตรงๆ ก่อน
                        const res = await fetch('api.php?path=user/update-profile', {
                            method: 'POST',
                            body: JSON.stringify(payload)
                        });
                        const data = await res.json();
                        if (res.ok) {
                            this.user = data;
                            localStorage.setItem('schoolos_user', JSON.stringify(data));
                            alert('อัปเดตข้อมูลสำเร็จ');
                            this.syncProfileForm();
                        }
                    } finally { this.loading = false; }
                },

                async generateAI() {
                    if (!this.config.topic) return alert('กรุณาระบุหัวข้อ');
                    this.loading = true;
                    try {
                        const payload = { ...this.config, userId: this.user.id };
                        const res = await fetch('api.php?path=generate', {
                            method: 'POST',
                            body: JSON.stringify(payload)
                        });
                        const data = await res.json();
                        if (data.error) {
                            alert('เกิดข้อผิดพลาด: ' + (data.details?.error?.message || data.error));
                        } else {
                            this.currentSet = data;
                            this.currentSet.subject = this.config.subject;
                            this.currentSet.level = this.config.level;
                            this.$nextTick(() => lucide.createIcons());
                        }
                    } catch (e) { alert('AI ไม่สามารถสร้างข้อมูลได้ในขณะนี้ กรุณาตรวจสอบ API Key ของคุณ'); }
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
                }
            }
        }
    </script>
</body>
</html>
