import React, { useState, useEffect } from 'react';
import { motion, AnimatePresence } from 'motion/react';
import { 
  FileText, 
  Plus, 
  Printer, 
  Save, 
  Trash2, 
  Sparkles, 
  GraduationCap, 
  User as UserIcon, 
  BookOpen, 
  History,
  ChevronLeft
} from 'lucide-react';
import { ExerciseSet, ExerciseType, User, TEACHER_RANKS } from './types';
import { generateExercises } from './services/geminiService';

export default function App() {
  const [user, setUser] = useState<User | null>(null);
  const [loginMode, setLoginMode] = useState<'login' | 'register'>('login');
  const [authData, setAuthData] = useState({ id_card: '', fullname: '', rank: TEACHER_RANKS[0], password: '' });
  
  const [activeTab, setActiveTab] = useState<'create' | 'history' | 'admin'>('create');
  const [topic, setTopic] = useState('');
  const [subject, setSubject] = useState('คณิตศาสตร์');
  const [level, setLevel] = useState('ประถมศึกษาปีที่ 1');
  const [teacherName, setTeacherName] = useState('');
  const [isLoading, setIsLoading] = useState(false);
  const [currentExercise, setCurrentExercise] = useState<ExerciseSet | null>(null);
  const [savedExercises, setSavedExercises] = useState<any[]>([]);
  const [pendingUsers, setPendingUsers] = useState<any[]>([]);
  const [allUsers, setAllUsers] = useState<any[]>([]);
  const [adminSubTab, setAdminSubTab] = useState<'pending' | 'users'>('pending');
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    if (user) {
      fetchSaved();
      if (user.is_admin) {
        fetchPendingUsers();
        fetchAllUsers();
      }
    }
  }, [user]);

  const fetchSaved = async () => {
    if (!user) return;
    try {
      const res = await fetch(`/api/exercises?userId=${user.id}`);
      const data = await res.json();
      setSavedExercises(data);
    } catch (err) {
      console.error(err);
    }
  };

  const fetchPendingUsers = async () => {
    try {
      const res = await fetch('/api/admin/pending-users');
      const data = await res.json();
      setPendingUsers(data || []);
    } catch (err) {
      console.error(err);
    }
  };

  const fetchAllUsers = async () => {
    try {
      const res = await fetch('/api/admin/all-users');
      const data = await res.json();
      setAllUsers(data || []);
    } catch (err) {
      console.error(err);
    }
  };

  const handleLogin = async (e: React.FormEvent) => {
    e.preventDefault();
    setError(null);
    try {
      const res = await fetch('/api/login', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id_card: authData.id_card, password: authData.password })
      });
      const data = await res.json();
      if (res.ok) {
        setUser(data);
        setTeacherName(data.fullname);
      } else {
        setError(data.error);
      }
    } catch (err) {
      setError('เกิดข้อผิดพลาดในการเชื่อมต่อ');
    }
  };

  const handleRegister = async (e: React.FormEvent) => {
    e.preventDefault();
    setError(null);
    try {
      const res = await fetch('/api/register', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(authData)
      });
      const data = await res.json();
      if (res.ok) {
        alert(data.message);
        setLoginMode('login');
      } else {
        setError(data.error);
      }
    } catch (err) {
      setError('เกิดข้อผิดพลาดในการเชื่อมต่อ');
    }
  };

  const handleApprove = async (id: number, status: 'approved' | 'rejected') => {
    try {
      await fetch('/api/admin/approve-user', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id, status })
      });
      fetchPendingUsers();
      fetchAllUsers();
    } catch (err) {
      alert('เกิดข้อผิดพลาด');
    }
  };

  const handleUpdateUserStatus = async (id: number, status: string) => {
    try {
      await fetch('/api/admin/update-user-status', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id, status })
      });
      fetchAllUsers();
      fetchPendingUsers();
    } catch (err) {
      alert('เกิดข้อผิดพลาด');
    }
  };

  const handleDeleteUser = async (id: number) => {
    if (!confirm('คุณต้องการลบข้อมูลสมาชิกรวมถึงใบงานทั้งหมดของสมาชิกท่านนี้ใช่หรือไม่? การกระทำนี้ไม่สามารถย้อนกลับได้')) return;
    try {
      await fetch(`/api/admin/users/${id}`, { method: 'DELETE' });
      fetchAllUsers();
      fetchPendingUsers();
    } catch (err) {
      alert('ไม่สามารถลบข้อมูลได้');
    }
  };

  const handleGenerate = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!topic) return;
    setIsLoading(true);
    setError(null);
    try {
      const result = await generateExercises(topic, level, subject);
      setCurrentExercise(result);
      setActiveTab('create');
    } catch (err) {
      setError('ไม่สามารถสร้างแบบฝึกหัดได้ในขณะนี้ กรุณาลองใหม่อีกครั้ง');
      console.error(err);
    } finally {
      setIsLoading(false);
    }
  };

  const handleSave = async () => {
    if (!currentExercise || !teacherName || !user) {
      alert('ข้อมูลไม่ครบถ้วน');
      return;
    }
    try {
      const res = await fetch('/api/exercises', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          title: currentExercise.title,
          content: currentExercise,
          teacher_name: teacherName,
          user_id: user.id
        })
      });
      if (res.ok) {
        alert('บันทึกสำเร็จ');
        fetchSaved();
      }
    } catch (err) {
      alert('เกิดข้อผิดพลาดในการบันทึก');
    }
  };

  const handlePrint = () => {
    window.print();
  };

  const handleDelete = async (id: number) => {
    if (!confirm('คุณต้องการลบแบบฝึกหัดนี้ใช่หรือไม่?')) return;
    try {
      await fetch(`/api/exercises/${id}`, { method: 'DELETE' });
      fetchSaved();
    } catch (err) {
      alert('ลบไม่สำเร็จ');
    }
  };

  const loadSaved = (item: any) => {
    setCurrentExercise(item.content);
    setTeacherName(item.teacher_name);
    setActiveTab('create');
  };

  // --- Auth Screen ---
  if (!user) {
    return (
      <div className="min-h-screen bg-slate-100 flex items-center justify-center p-4">
        <motion.div 
          initial={{ opacity: 0, scale: 0.95 }}
          animate={{ opacity: 1, scale: 1 }}
          className="bg-white p-8 rounded-3xl shadow-xl w-full max-w-md border border-slate-200"
        >
          <div className="flex flex-col items-center mb-8">
             <div className="w-12 h-12 bg-indigo-600 rounded-2xl flex items-center justify-center text-white mb-4 shadow-lg shadow-indigo-100">
               <GraduationCap size={28} />
             </div>
             <h1 className="text-2xl font-bold text-slate-800">AI EduGenerator</h1>
             <p className="text-slate-500 text-sm mt-1">{loginMode === 'login' ? 'เข้าสู่ระบบคุณครู' : 'ลงทะเบียนบัญชีใหม่'}</p>
          </div>

          <form onSubmit={loginMode === 'login' ? handleLogin : handleRegister} className="space-y-4">
             <div>
                <label className="block text-xs font-bold text-slate-400 uppercase mb-1">หมายเลขประจำตัวประชาชน</label>
                <input 
                  type="text"
                  value={authData.id_card}
                  onChange={e => setAuthData({...authData, id_card: e.target.value})}
                  className="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none"
                  placeholder="ID Card Number"
                  required
                />
             </div>

             {loginMode === 'register' && (
               <>
                 <div>
                    <label className="block text-xs font-bold text-slate-400 uppercase mb-1">ชื่อ-นามสกุล</label>
                    <input 
                      type="text"
                      value={authData.fullname}
                      onChange={e => setAuthData({...authData, fullname: e.target.value})}
                      className="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none"
                      placeholder="Full Name"
                      required
                    />
                 </div>
                 <div>
                    <label className="block text-xs font-bold text-slate-400 uppercase mb-1">ตำแหน่ง</label>
                    <select 
                      value={authData.rank}
                      onChange={e => setAuthData({...authData, rank: e.target.value})}
                      className="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none"
                    >
                      {TEACHER_RANKS.map(r => <option key={r} value={r}>{r}</option>)}
                    </select>
                 </div>
               </>
             )}

             <div>
                <label className="block text-xs font-bold text-slate-400 uppercase mb-1">รหัสผ่าน</label>
                <input 
                  type="password"
                  value={authData.password}
                  onChange={e => setAuthData({...authData, password: e.target.value})}
                  className="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none"
                  placeholder="Password"
                  required
                />
             </div>

             {error && <p className="text-red-500 text-xs text-center">{error}</p>}

             <button type="submit" className="w-full py-4 bg-indigo-600 text-white rounded-2xl font-bold shadow-lg shadow-indigo-100 hover:bg-indigo-700 transition-all active:scale-95">
               {loginMode === 'login' ? 'เข้าสู่ระบบ' : 'ลงทะเบียน'}
             </button>
          </form>

          <div className="mt-6 text-center">
             <button 
              onClick={() => { setLoginMode(loginMode === 'login' ? 'register' : 'login'); setError(null); }}
              className="text-indigo-600 text-sm font-medium hover:underline"
             >
               {loginMode === 'login' ? 'ยังไม่มีบัญชี? ลงทะเบียนที่นี่' : 'มีบัญชีอยู่แล้ว? เข้าสู่ระบบ'}
             </button>
          </div>
          
          {loginMode === 'login' && (
            <div className="mt-8 pt-6 border-t border-slate-100 text-[10px] text-slate-400 text-center uppercase tracking-widest leading-loose">
               Default Admin สำหรับทดสอบ:<br/> ID: admin | Pass: admin123
            </div>
          )}
        </motion.div>
      </div>
    );
  }

  return (
    <div className="flex flex-col h-screen w-full bg-slate-100 font-sans text-slate-900 overflow-hidden">
      {/* Navigation */}
      <nav className="no-print h-16 bg-white border-b border-slate-200 flex items-center justify-between px-6 flex-shrink-0 z-50">
        <div className="flex items-center gap-3">
          <div className="w-8 h-8 bg-indigo-600 rounded flex items-center justify-center text-white font-bold">
            <GraduationCap size={20} />
          </div>
          <h1 className="text-xl font-semibold tracking-tight text-slate-800">
            AI EduGenerator <span className="text-indigo-600 font-bold">Member Systems</span>
          </h1>
        </div>
        <div className="flex items-center gap-4">
          <div className="flex bg-slate-100 p-1 rounded-md">
            <button 
              onClick={() => setActiveTab('create')}
              className={`px-4 py-1.5 rounded text-sm font-medium transition-all ${activeTab === 'create' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-500 hover:text-slate-700'}`}
            >
              <div className="flex items-center gap-1.5">
                <Plus size={16} /> สร้างใหม่
              </div>
            </button>
            <button 
              onClick={() => setActiveTab('history')}
              className={`px-4 py-1.5 rounded text-sm font-medium transition-all ${activeTab === 'history' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-500 hover:text-slate-700'}`}
            >
              <div className="flex items-center gap-1.5">
                <History size={16} /> ประวัติ
              </div>
            </button>
            {user.is_admin && (
              <button 
                onClick={() => setActiveTab('admin')}
                className={`px-4 py-1.5 rounded text-sm font-medium transition-all ${activeTab === 'admin' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-500 hover:text-slate-700'}`}
              >
                <div className="flex items-center gap-1.5 font-bold">
                  อนุมัติ ({pendingUsers.length})
                </div>
              </button>
            )}
          </div>
          
          <div className="flex items-center gap-3 pl-4 border-l border-slate-200">
             <div className="text-right no-print hidden sm:block">
                <p className="text-xs font-bold text-slate-800 leading-none">{user.fullname}</p>
                <p className="text-[10px] text-slate-400 mt-1 uppercase tracking-tighter">{user.rank}</p>
             </div>
             <button 
               onClick={() => { setUser(null); setActiveTab('create'); }}
               className="w-8 h-8 rounded-full bg-slate-200 border border-slate-300 flex items-center justify-center text-slate-500 hover:bg-slate-300 transition-all no-print"
               title="ลงชื่อออก"
             >
               <UserIcon size={16} />
             </button>
          </div>
        </div>
      </nav>

      <main className="flex flex-1 overflow-hidden p-6 gap-6">
        <AnimatePresence mode="wait">
          {activeTab === 'create' && (
            <motion.div 
              key="create-shell"
              initial={{ opacity: 0 }}
              animate={{ opacity: 1 }}
              exit={{ opacity: 0 }}
              className="flex flex-1 overflow-hidden gap-6"
            >
              <aside className="no-print w-80 flex flex-col gap-4 flex-shrink-0">
                <div className="bg-white rounded-xl shadow-polish border border-slate-200 p-5 flex flex-col gap-4">
                  <h2 className="text-sm font-bold uppercase tracking-wider text-slate-400">AI Generator Settings</h2>
                  
                  <form onSubmit={handleGenerate} className="space-y-4">
                    <div>
                      <label className="block text-xs font-medium text-slate-700 mb-1">หัวข้อเรื่องที่ต้องการสอน</label>
                      <textarea 
                        placeholder="เช่น ระบบสุริยะ, การบวกลบเลขไม่เกิน 100"
                        value={topic}
                        onChange={(e) => setTopic(e.target.value)}
                        className="w-full h-24 bg-slate-50 border border-slate-200 rounded-md px-3 py-2 text-sm resize-none text-slate-600 focus:ring-1 focus:ring-indigo-500 outline-none"
                        required
                      />
                    </div>
                    
                    <div>
                      <label className="block text-xs font-medium text-slate-700 mb-1">วิชา</label>
                      <select 
                        value={subject}
                        onChange={(e) => setSubject(e.target.value)}
                        className="w-full bg-slate-50 border border-slate-200 rounded-md px-3 py-2 text-sm focus:ring-1 focus:ring-indigo-500 outline-none"
                      >
                        <option>คณิตศาสตร์</option>
                        <option>ภาษาไทย</option>
                        <option>วิทยาศาสตร์</option>
                        <option>ภาษาอังกฤษ</option>
                        <option>สังคมศึกษา</option>
                      </select>
                    </div>

                    <div>
                      <label className="block text-xs font-medium text-slate-700 mb-1">ระดับชั้น</label>
                      <select 
                        value={level}
                        onChange={(e) => setLevel(e.target.value)}
                        className="w-full bg-slate-50 border border-slate-200 rounded-md px-3 py-2 text-sm focus:ring-1 focus:ring-indigo-500 outline-none"
                      >
                        {[1,2,3,4,5,6].map(i => (
                            <option key={i} value={`ประถมศึกษาปีที่ ${i}`}>ประถมศึกษาปีที่ {i}</option>
                        ))}
                      </select>
                    </div>

                    <button 
                      type="submit"
                      disabled={isLoading}
                      className="w-full bg-indigo-600 text-white py-2.5 rounded-lg font-bold text-sm shadow-md hover:shadow-indigo-100 disabled:bg-indigo-300 transition-all flex items-center justify-center gap-2"
                    >
                      {isLoading ? (
                        <>
                          <div className="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin" />
                          กำลังสร้าง...
                        </>
                      ) : (
                        <>
                          <Sparkles size={16} /> ✨ Generate with AI
                        </>
                      )}
                    </button>
                  </form>

                  {error && (
                    <div className="mt-2 p-3 bg-red-50 text-red-600 rounded-lg text-xs border border-red-100">
                      {error}
                    </div>
                  )}
                </div>

                <div className="bg-white rounded-xl shadow-polish border border-slate-200 p-5 flex-1 overflow-hidden flex flex-col no-print">
                  <h2 className="text-sm font-bold uppercase tracking-wider text-slate-400 mb-4">ครูผู้สร้าง</h2>
                  <div className="space-y-3">
                    <div>
                      <label className="block text-xs font-medium text-slate-700 mb-1">ชื่อคุณครู</label>
                      <div className="relative">
                        <UserIcon className="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" size={14} />
                        <input 
                          type="text"
                          placeholder="ชื่อ-นามสกุล..."
                          value={teacherName}
                          onChange={(e) => setTeacherName(e.target.value)}
                          className="w-full pl-9 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-md text-sm outline-none focus:ring-1 focus:ring-indigo-500"
                        />
                      </div>
                    </div>
                  </div>
                  {currentExercise && (
                    <div className="mt-auto pt-4 space-y-2">
                       <button 
                        onClick={handleSave}
                        className="w-full px-4 py-2 bg-white border border-slate-200 rounded-md text-sm font-medium shadow-sm flex items-center justify-center gap-2 hover:bg-slate-50 transition-colors"
                       >
                         <Save size={16} /> บันทึกข้อมูล
                       </button>
                       <button 
                        onClick={handlePrint}
                        className="w-full px-4 py-2 bg-slate-800 text-white rounded-md text-sm font-medium shadow-sm flex items-center justify-center gap-2 hover:bg-slate-900 transition-colors"
                       >
                         <Printer size={16} /> พิมพ์ใบงาน
                       </button>
                    </div>
                  )}
                </div>
              </aside>

              <section className="flex-1 flex flex-col gap-4 overflow-hidden">
                {!currentExercise ? (
                   <div className="flex-1 bg-white rounded-xl border border-dashed border-slate-300 flex flex-col items-center justify-center text-slate-400 gap-4">
                      <div className="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center border border-slate-200">
                        <Sparkles size={32} className="text-indigo-300" />
                      </div>
                      <div className="text-center">
                        <p className="font-semibold text-slate-600">พร้อมสร้างแบบฝึกหัดแล้ว</p>
                        <p className="text-sm">กรอกหัวข้อที่ต้องการในแถบด้านซ้าย</p>
                      </div>
                   </div>
                ) : (
                  <>
                    <div className="no-print flex items-center justify-between">
                       <button 
                         onClick={() => setCurrentExercise(null)}
                         className="px-3 py-1.5 text-xs font-medium text-slate-500 hover:text-indigo-600 flex items-center gap-1 transition-colors"
                       >
                         <ChevronLeft size={14} /> ยกเลิกพรีวิว
                       </button>
                       <div className="text-[10px] text-slate-400 font-bold uppercase tracking-widest">Worksheet Preview Mode</div>
                    </div>

                    <div className="flex-1 bg-slate-300 rounded-xl p-8 flex justify-center shadow-inner overflow-hidden">
                      <div className="print-area w-full max-w-[210mm] bg-white shadow-2xl h-full p-12 flex flex-col overflow-y-auto no-scrollbar scroll-smooth">
                        {/* Worksheet Header */}
                        <div className="border-b-2 border-slate-800 pb-4 mb-8">
                           <div className="flex justify-between items-start mb-6">
                              <div className="space-y-3">
                                <div className="space-y-1">
                                  <p className="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Student Name / ชื่อ-นามสกุล:</p>
                                  <div className="w-64 h-6 border-b border-dotted border-slate-400"></div>
                                </div>
                                <div className="flex gap-4">
                                  <div className="space-y-1">
                                    <p className="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Class / ห้อง:</p>
                                    <div className="w-16 h-6 border-b border-dotted border-slate-400"></div>
                                  </div>
                                  <div className="space-y-1">
                                    <p className="text-[10px] font-bold text-slate-400 uppercase tracking-wider">No / เลขที่:</p>
                                    <div className="w-12 h-6 border-b border-dotted border-slate-400"></div>
                                  </div>
                                </div>
                              </div>
                              <div className="text-right flex flex-col items-end gap-2">
                                 <div className="w-16 h-16 border-2 border-slate-200 rounded flex flex-col items-center justify-center p-1">
                                    <p className="text-[8px] font-bold text-slate-400 uppercase leading-none mb-1">Score</p>
                                    <p className="text-lg font-bold text-slate-300">/</p>
                                 </div>
                              </div>
                           </div>
                           <h3 className="text-center font-bold text-xl underline underline-offset-8">แบบฝึกหัดวิชา{currentExercise.subject}: เรื่อง{currentExercise.title}</h3>
                        </div>

                        {/* Worksheet Content */}
                        <div className="flex-1 space-y-10">
                          {currentExercise.questions.map((q, index) => (
                            <div key={q.id} className="break-inside-avoid">
                              <div className="flex gap-4">
                                <span className="font-bold text-base min-w-[24px]">{index + 1}.</span>
                                <div className="flex-1">
                                  <p className="text-base text-slate-900 font-medium mb-3 leading-relaxed">{q.question}</p>
                                  
                                  {q.type === ExerciseType.MULTIPLE_CHOICE && (
                                    <div className="grid grid-cols-2 gap-x-4 gap-y-2 ml-2">
                                      {q.options?.map((opt, i) => (
                                        <div key={opt.id} className="flex items-center gap-2 text-sm">
                                          <div className="w-5 h-5 border border-slate-400 rounded-full flex items-center justify-center text-[10px] font-bold">
                                            {String.fromCharCode(65 + i)}
                                          </div>
                                          <span className="text-slate-800">{opt.text}</span>
                                        </div>
                                      ))}
                                    </div>
                                  )}

                                  {(q.type === ExerciseType.FILL_IN_THE_BLANKS || q.type === ExerciseType.SUBJECTIVE) && (
                                    <div className="mt-4 space-y-4">
                                       <div className="border-b border-dotted border-slate-400 w-full"></div>
                                       {q.type === ExerciseType.SUBJECTIVE && <div className="border-b border-dotted border-slate-400 w-full"></div>}
                                    </div>
                                  )}

                                  {q.type === ExerciseType.MATCHING && (
                                    <div className="mt-3 grid grid-cols-2 gap-x-12 gap-y-3 text-sm">
                                       <div className="space-y-3">
                                          {q.pairs?.map((p, i) => (
                                            <div key={i} className="flex items-center gap-3">
                                              <span className="w-3.5 h-3.5 border border-slate-300"></span>
                                              <span>{p.left}</span>
                                            </div>
                                          ))}
                                       </div>
                                       <div className="space-y-3 text-right">
                                          {q.pairs?.slice().sort(() => Math.random() - 0.5).map((p, i) => (
                                            <div key={i} className="flex items-center justify-end gap-3">
                                              <span>{p.right}</span>
                                              <span className="w-3.5 h-3.5 border border-slate-300"></span>
                                            </div>
                                          ))}
                                       </div>
                                    </div>
                                  )}

                                  {q.type === ExerciseType.MATH_SHOW_WORK && (
                                    <div className="mt-2 border border-slate-200 bg-slate-50/50 rounded p-4 min-h-[140px] relative">
                                       <p className="text-[10px] font-bold text-slate-300 uppercase tracking-widest absolute top-2 left-3">Calculation / แสดงวิธีทำ</p>
                                       <div className="h-full pt-6 flex flex-col justify-end">
                                          <div className="text-right text-sm">ตอบ ..............................................................</div>
                                       </div>
                                    </div>
                                  )}

                                  {q.type === ExerciseType.ANALYSIS_REASONING && (
                                    <div className="mt-3 space-y-1">
                                       <p className="text-xs font-bold text-slate-400 uppercase mb-1">Reasoning / วิเคราะห์เหตุผล</p>
                                       <div className="w-full h-20 border border-slate-200 bg-slate-50 rounded italic text-slate-300 p-3 text-xs leading-relaxed">
                                          ให้เหตุผลประกอบคำตอบ...
                                       </div>
                                    </div>
                                  )}
                                </div>
                              </div>
                            </div>
                          ))}
                        </div>

                        {/* Worksheet Footer */}
                        <div className="mt-auto pt-8 border-t border-slate-200 flex justify-between items-end">
                          <div className="text-[9px] text-slate-400 font-bold uppercase tracking-wider">
                            Generated by AI EduGenerator Thai • {new Date().toLocaleDateString('th-TH')}
                          </div>
                          <div className="text-right">
                            <p className="text-[10px] font-bold">คุณครูผู้สร้าง: <span className="underline underline-offset-2 ml-1">{teacherName || '...........................................'}</span></p>
                            <p className="text-[8px] text-slate-400 uppercase tracking-widest mt-1">Elementary Education Department</p>
                          </div>
                        </div>
                      </div>
                    </div>
                  </>
                )}
              </section>
            </motion.div>
          )}

          {activeTab === 'admin' && user && user.is_admin && (
            <motion.div 
              key="admin-shell"
              initial={{ opacity: 0 }}
              animate={{ opacity: 1 }}
              exit={{ opacity: 0 }}
              className="flex-1 overflow-y-auto no-scrollbar px-2"
            >
              <div className="bg-white rounded-2xl shadow-polish border border-slate-200 overflow-hidden">
                <div className="p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                   <div className="flex items-center gap-4">
                      <div className="w-10 h-10 bg-amber-50 rounded-lg flex items-center justify-center text-amber-500">
                        <UserIcon size={20} />
                      </div>
                      <div>
                        <h2 className="text-lg font-bold text-slate-800">ระบบจัดการผู้ดูแล (Super Admin)</h2>
                        <p className="text-sm text-slate-500">จัดการการอนุมัติและสมาชิกทั้งหมดในระบบ</p>
                      </div>
                   </div>
                   
                   <div className="flex bg-slate-100 p-1 rounded-lg no-print">
                      <button 
                        onClick={() => setAdminSubTab('pending')}
                        className={`px-4 py-1.5 rounded-md text-xs font-bold transition-all flex items-center gap-2 ${adminSubTab === 'pending' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-500 hover:text-slate-700'}`}
                      >
                        รอการอนุมัติ
                        {pendingUsers.length > 0 && <span className="bg-red-500 text-white rounded-full w-4 h-4 flex items-center justify-center text-[10px]">{pendingUsers.length}</span>}
                      </button>
                      <button 
                        onClick={() => setAdminSubTab('users')}
                        className={`px-4 py-1.5 rounded-md text-xs font-bold transition-all ${adminSubTab === 'users' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-500 hover:text-slate-700'}`}
                      >
                        จัดการสมาชิกทั้งหมด
                      </button>
                   </div>
                </div>
                
                <div className="divide-y divide-slate-100">
                  {adminSubTab === 'pending' ? (
                    pendingUsers.length === 0 ? (
                      <div className="py-20 text-center text-slate-400">
                         <p className="text-sm">ไม่มีคำขอที่รอการอนุมัติในขณะนี้</p>
                      </div>
                    ) : (
                      pendingUsers.map((p) => (
                        <div key={p.id} className="p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:bg-slate-50/50 transition-colors">
                           <div className="flex items-center gap-4">
                              <div className="w-12 h-12 bg-indigo-50 rounded-full flex items-center justify-center text-indigo-600 border border-indigo-100 uppercase font-bold text-lg">
                                 {p.fullname.charAt(0)}
                              </div>
                              <div>
                                 <p className="font-bold text-slate-800">{p.fullname}</p>
                                 <div className="flex flex-wrap gap-2 text-[10px] text-slate-500 mt-1">
                                    <span className="bg-slate-100 px-1.5 py-0.5 rounded">ID: {p.id_card}</span>
                                    <span className="bg-indigo-50 text-indigo-600 px-1.5 py-0.5 rounded font-bold">{p.rank}</span>
                                    <span className="bg-slate-100 px-1.5 py-0.5 rounded italic">{new Date(p.created_at).toLocaleDateString('th-TH')}</span>
                                 </div>
                              </div>
                           </div>
                           <div className="flex items-center gap-2">
                              <button 
                                onClick={() => handleApprove(p.id, 'approved')}
                                className="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-bold shadow-sm hover:bg-indigo-700 transition-all"
                              >
                                อนุมัติ
                              </button>
                              <button 
                                onClick={() => handleDeleteUser(p.id)}
                                className="px-4 py-2 bg-white border border-slate-200 text-red-500 rounded-lg text-sm font-bold shadow-sm hover:bg-red-50 transition-all"
                              >
                                ลบออก
                              </button>
                           </div>
                        </div>
                      ))
                    )
                  ) : (
                    allUsers.length === 0 ? (
                      <div className="py-20 text-center text-slate-400">
                         <p className="text-sm">ยังไม่มีสมาชิกในระบบ</p>
                      </div>
                    ) : (
                      allUsers.map((p) => (
                        <div key={p.id} className="p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:bg-slate-50/50 transition-colors">
                           <div className="flex items-center gap-4">
                              <div className="w-10 h-10 bg-slate-100 rounded-full flex items-center justify-center text-slate-500 border border-slate-200 uppercase font-bold text-base">
                                 {p.fullname.charAt(0)}
                              </div>
                              <div>
                                 <div className="flex items-center gap-2">
                                   <p className="font-bold text-slate-800">{p.fullname}</p>
                                   <span className={`text-[9px] font-bold uppercase py-0.5 px-2 rounded-full ${
                                      p.status === 'approved' ? 'bg-green-100 text-green-700' : 
                                      p.status === 'pending' ? 'bg-amber-100 text-amber-700' : 
                                      'bg-red-100 text-red-700'
                                   }`}>
                                     {p.status}
                                   </span>
                                 </div>
                                 <div className="flex flex-wrap gap-2 text-[10px] text-slate-500 mt-1">
                                    <span>{p.id_card}</span>
                                    <span>•</span>
                                    <span>{p.rank}</span>
                                 </div>
                              </div>
                           </div>
                           <div className="flex items-center gap-2">
                              {p.status === 'approved' ? (
                                <button 
                                  onClick={() => handleUpdateUserStatus(p.id, 'rejected')}
                                  className="px-3 py-1.5 bg-amber-50 text-amber-600 rounded text-[10px] font-bold uppercase tracking-wider hover:bg-amber-100 transition-colors"
                                >
                                  ระงับการใช้งาน
                                </button>
                              ) : (
                                <button 
                                  onClick={() => handleUpdateUserStatus(p.id, 'approved')}
                                  className="px-3 py-1.5 bg-green-50 text-green-600 rounded text-[10px] font-bold uppercase tracking-wider hover:bg-green-100 transition-colors"
                                >
                                  อนุมัติ
                                </button>
                              )}
                              <button 
                                onClick={() => handleDeleteUser(p.id)}
                                className="px-3 py-1.5 bg-red-50 text-red-500 rounded text-[10px] font-bold uppercase tracking-wider hover:bg-red-100 transition-colors"
                              >
                                ลบสมาชิก
                              </button>
                           </div>
                        </div>
                      ))
                    )
                  )}
                </div>
              </div>
            </motion.div>
          )}

          {activeTab === 'history' && (
            <motion.div 
              key="history-shell"
              initial={{ opacity: 0, scale: 0.98 }}
              animate={{ opacity: 1, scale: 1 }}
              exit={{ opacity: 0, scale: 0.98 }}
              className="flex-1 overflow-y-auto no-scrollbar px-2"
            >
              <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 pb-12">
                {savedExercises.length === 0 ? (
                  <div className="col-span-full py-32 text-center">
                    <div className="w-20 s-20 bg-white rounded-2xl shadow-sm border border-slate-200 flex items-center justify-center mx-auto text-slate-300 mb-4">
                      <FileText size={40} />
                    </div>
                    <h3 className="text-slate-600 font-bold text-lg">คลังใบงานว่างเปล่า</h3>
                    <p className="text-slate-400 text-sm mt-1 mb-6">คุณยังไม่ได้บันทึกแบบฝึกหัดใดๆ ไว้ในระบบ</p>
                    <button 
                      onClick={() => setActiveTab('create')}
                      className="px-6 py-2 bg-indigo-600 text-white rounded-lg font-bold text-sm shadow-md hover:bg-indigo-700 transition-all active:scale-95"
                    >
                      สร้างใบงานแรกของคุณ
                    </button>
                  </div>
                ) : (
                  savedExercises.map((item) => (
                    <div key={item.id} className="bg-white rounded-xl p-5 shadow-polish border border-slate-200 hover:border-indigo-200 transition-all group flex flex-col">
                      <div className="flex justify-between items-start mb-4">
                        <div className="w-10 h-10 bg-indigo-50 rounded-lg flex items-center justify-center text-indigo-500">
                          <FileText size={20} />
                        </div>
                        <button 
                          onClick={(e) => { e.stopPropagation(); handleDelete(item.id); }}
                          className="p-1.5 text-slate-300 hover:text-red-500 hover:bg-red-50 rounded transition-all"
                        >
                          <Trash2 size={16} />
                        </button>
                      </div>
                      
                      <div className="flex-1">
                        <h3 className="text-base font-bold text-slate-800 line-clamp-2 leading-tight mb-2">{item.title}</h3>
                        <p className="text-xs text-slate-500 flex items-center gap-1">
                          <UserIcon size={12} /> คุณครู{item.teacher_name}
                        </p>
                      </div>

                      <div className="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between">
                         <span className="text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                           {new Date(item.created_at).toLocaleDateString('th-TH')}
                         </span>
                         <button 
                          onClick={() => loadSaved(item)}
                          className="px-3 py-1.5 bg-slate-900 text-white rounded text-[10px] font-bold hover:bg-slate-800 transition-all uppercase tracking-widest"
                         >
                           Open View
                         </button>
                      </div>
                    </div>
                  ))
                )}
              </div>
            </motion.div>
          )}
        </AnimatePresence>
      </main>
    </div>
  );
}
