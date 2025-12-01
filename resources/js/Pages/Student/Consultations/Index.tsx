import StudentLayout from '@/Layouts/StudentLayout';
import { Head, router, useForm, usePage } from '@inertiajs/react';
import { Consultation } from '@/Types';
import { route } from 'ziggy-js';
import Button from '@/Components/Button';
import LoadingSpinner from '@/Components/LoadingSpinner';
import { useState, useEffect } from 'react';

interface ConsultationsIndexProps {
    consultations: Consultation[];
    existing_consultation?: Consultation | null;
}

export default function Index({ consultations: initialConsultations, existing_consultation: initialExisting }: ConsultationsIndexProps) {
    const page = usePage();
    const flash = (page.props as any).flash || {};
    const [consultations, setConsultations] = useState(initialConsultations);
    const [isLoading, setIsLoading] = useState(false);

    useEffect(() => {
        setConsultations(initialConsultations);
        // 既存の相談があった場合、リストに追加（まだ存在しない場合）
        if (initialExisting) {
            const exists = initialConsultations.some(c => c.id === initialExisting.id);
            if (!exists) {
                setConsultations([initialExisting, ...initialConsultations]);
            }
        }
    }, [initialConsultations, initialExisting]);

    const { data, setData, post, processing, errors, reset } = useForm({
        question: '',
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        setIsLoading(true);

        post(route('student.consultations.store'), {
            preserveScroll: true,
            onSuccess: () => {
                reset();
                setIsLoading(false);
                router.reload({ only: ['consultations'] });
            },
            onError: () => {
                setIsLoading(false);
            },
            onFinish: () => {
                setIsLoading(false);
            },
        });
    };

    return (
        <StudentLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    相談チャット
                </h2>
            }
        >
            <Head title="相談チャット" />

            <div className="py-12">
                <div className="mx-auto max-w-4xl sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            {/* 固定質問文 */}
                            <div className="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-md">
                                <h3 className="text-lg font-semibold text-gray-900 mb-2">
                                    今どんな状態ですか？何をオズに相談したいですか？
                                </h3>
                                <p className="text-sm text-gray-700 mb-2">
                                    例えば、
                                </p>
                                <ul className="list-disc list-inside text-sm text-gray-700 space-y-1 mb-3">
                                    <li>どんな商品を出せばいいかわからない</li>
                                    <li>商品案をなんとなく思いついているけどどう進めればいいかわからない</li>
                                    <li>競合リサーチや商品設計が思い浮かばない</li>
                                    <li>どんな商品ならどれくらいの売り上げが見込めるのかリサーチしてもわからない</li>
                                    <li>プロフや商品説明が作れない</li>
                                    <li>サムネや画像類のアイディアが思いつかない</li>
                                </ul>
                                <p className="text-sm text-gray-700">
                                    など、ざっくばらんにご相談ください！
                                </p>
                            </div>

                            {/* 注意文 */}
                            <div className="mb-6 p-3 bg-yellow-50 border border-yellow-200 rounded-md">
                                <p className="text-sm text-yellow-800">
                                    ※同じ質問をされた場合は、過去に同じ質問をした方と同じ回答が返ってきますのでご注意ください
                                </p>
                            </div>

                            {/* フォーム */}
                            <form onSubmit={handleSubmit} className="mb-8">
                                <div className="mb-4">
                                    <label htmlFor="question" className="block text-sm font-medium text-gray-700 mb-2">
                                        ご質問
                                    </label>
                                    <textarea
                                        id="question"
                                        className="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        rows={10}
                                        value={data.question}
                                        onChange={(e) => setData('question', e.target.value)}
                                        required
                                        placeholder="ご質問を入力してください"
                                    />
                                    {errors.question && (
                                        <p className="mt-1 text-sm text-red-600">{errors.question}</p>
                                    )}
                                </div>

                                {flash?.warning && (
                                    <div className="mb-4 p-3 bg-yellow-50 border border-yellow-200 rounded-md flex items-center">
                                        <svg className="w-5 h-5 text-yellow-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fillRule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clipRule="evenodd" />
                                        </svg>
                                        <span className="text-sm text-yellow-800">
                                            {flash.warning}
                                        </span>
                                    </div>
                                )}
                                {flash?.success && (
                                    <div className="mb-4 p-3 bg-green-50 border border-green-200 rounded-md">
                                        <span className="text-sm text-green-800">
                                            {flash.success}
                                        </span>
                                    </div>
                                )}
                                {flash?.error && (
                                    <div className="mb-4 p-3 bg-red-50 border border-red-200 rounded-md">
                                        <span className="text-sm text-red-800">
                                            {flash.error}
                                        </span>
                                    </div>
                                )}

                                <div className="flex justify-end">
                                    <Button type="submit" disabled={processing || isLoading}>
                                        {isLoading ? '回答を生成中...' : '送信'}
                                    </Button>
                                </div>

                                {isLoading && (
                                    <div className="mt-4 flex justify-center">
                                        <LoadingSpinner message="回答を生成しています..." />
                                    </div>
                                )}
                            </form>

                            {/* チャット履歴 */}
                            {consultations.length > 0 && (
                                <div className="mt-8 border-t pt-8">
                                    <h3 className="text-lg font-semibold text-gray-900 mb-4">相談履歴</h3>
                                    <div className="space-y-4">
                                        {consultations.map((consultation) => (
                                            <div key={consultation.id} className="space-y-2">
                                                {/* 質問（右側） */}
                                                <div className="flex justify-end">
                                                    <div className="max-w-2xl bg-blue-100 rounded-lg p-4">
                                                        <p className="text-sm text-gray-700 whitespace-pre-wrap">
                                                            {consultation.question}
                                                        </p>
                                                        <p className="text-xs text-gray-500 mt-2 text-right">
                                                            {new Date(consultation.created_at).toLocaleString('ja-JP')}
                                                        </p>
                                                    </div>
                                                </div>
                                                {/* 回答（左側） */}
                                                <div className="flex justify-start">
                                                    <div className="max-w-2xl bg-gray-100 rounded-lg p-4">
                                                        <p className="text-sm text-gray-700 whitespace-pre-wrap">
                                                            {consultation.answer}
                                                        </p>
                                                        {consultation.is_corrected && (
                                                            <p className="text-xs text-orange-600 mt-2">
                                                                ※この回答は修正されています
                                                            </p>
                                                        )}
                                                    </div>
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </StudentLayout>
    );
}

