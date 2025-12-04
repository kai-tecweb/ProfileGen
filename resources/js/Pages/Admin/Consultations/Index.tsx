import AdminV2Layout from '@/Layouts/AdminV2Layout';
import { Head, router, useForm } from '@inertiajs/react';
import { PageProps, Consultation } from '@/Types';
import { route } from 'ziggy-js';
import Button from '@/Components/Button';
import Modal from '@/Components/Modal';
import InputLabel from '@/Components/InputLabel';
import InputError from '@/Components/InputError';
import { useState } from 'react';
import ReactMarkdown from 'react-markdown';
import remarkGfm from 'remark-gfm';
import { Prism as SyntaxHighlighter } from 'react-syntax-highlighter';
import { vscDarkPlus } from 'react-syntax-highlighter/dist/esm/styles/prism';

interface AdminConsultationsIndexProps extends PageProps {
    consultations: {
        data: Consultation[];
        current_page: number;
        last_page: number;
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
}

export default function Index({ consultations }: AdminConsultationsIndexProps) {
    const [showModal, setShowModal] = useState(false);
    const [selectedConsultation, setSelectedConsultation] = useState<Consultation | null>(null);
    const [expandedDetail, setExpandedDetail] = useState(false);

    const { data, setData, post, processing, errors, reset } = useForm({
        correct_answer: '',
        corrected_by: '',
    });

    const handleCorrect = (consultation: Consultation) => {
        setSelectedConsultation(consultation);
        setData({
            correct_answer: '',
            corrected_by: '',
        });
        setExpandedDetail(false);
        setShowModal(true);
    };

    // 回答を4セクションに分割
    const splitAnswer = (answer: string) => {
        const summaryMatch = answer.match(/【要約】\s*([\s\S]*?)(?=【今すぐやること】|【アドバイス】|【詳細】|$)/);
        const actionMatch = answer.match(/【今すぐやること】\s*([\s\S]*?)(?=【アドバイス】|【詳細】|$)/);
        const adviceMatch = answer.match(/【アドバイス】\s*([\s\S]*?)(?=【詳細】|$)/);
        const detailMatch = answer.match(/【詳細】\s*([\s\S]*)/);
        
        return {
            summary: summaryMatch ? summaryMatch[1].trim() : '',
            action: actionMatch ? actionMatch[1].trim() : '',
            advice: adviceMatch ? adviceMatch[1].trim() : '',
            detail: detailMatch ? detailMatch[1].trim() : answer
        };
    };

    const handleSubmitCorrection = (e: React.FormEvent) => {
        e.preventDefault();
        if (!selectedConsultation) return;

        post(route('admin.consultations.correct', selectedConsultation.id), {
            preserveScroll: true,
            onSuccess: () => {
                setShowModal(false);
                setSelectedConsultation(null);
                reset();
                router.reload();
            },
        });
    };

    const handleNotifyDiscord = (consultationId: number) => {
        if (confirm('Discordに投稿しますか？')) {
            router.post(route('admin.consultations.notify', consultationId));
        }
    };

    return (
        <AdminV2Layout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    相談履歴管理
                </h2>
            }
        >
            <Head title="相談履歴管理" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            {consultations.data.length > 0 ? (
                                <>
                                    <div className="overflow-x-auto">
                                        <table className="min-w-full divide-y divide-gray-200">
                                            <thead className="bg-gray-50">
                                                <tr>
                                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        質問
                                                    </th>
                                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        回答
                                                    </th>
                                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        日時
                                                    </th>
                                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        修正済み
                                                    </th>
                                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        操作
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody className="bg-white divide-y divide-gray-200">
                                                {consultations.data.map((consultation) => (
                                                    <tr key={consultation.id}>
                                                        <td className="px-6 py-4 text-sm text-gray-900 max-w-md">
                                                            <div className="whitespace-pre-wrap break-words">
                                                                {consultation.question.length > 100
                                                                    ? consultation.question.substring(0, 100) + '...'
                                                                    : consultation.question}
                                                            </div>
                                                        </td>
                                                        <td className="px-6 py-4 text-sm text-gray-900 max-w-md">
                                                            <div className="whitespace-pre-wrap break-words">
                                                                {consultation.answer.length > 100
                                                                    ? consultation.answer.substring(0, 100) + '...'
                                                                    : consultation.answer}
                                                            </div>
                                                        </td>
                                                        <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                            {new Date(consultation.created_at).toLocaleString('ja-JP')}
                                                        </td>
                                                        <td className="px-6 py-4 whitespace-nowrap text-sm">
                                                            {consultation.is_corrected ? (
                                                                <span className="px-2 py-1 text-xs font-semibold rounded-full bg-orange-100 text-orange-800">
                                                                    修正済み
                                                                </span>
                                                            ) : (
                                                                <span className="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                                                    未修正
                                                                </span>
                                                            )}
                                                        </td>
                                                        <td className="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                                            <Button
                                                                variant="secondary"
                                                                onClick={() => handleCorrect(consultation)}
                                                            >
                                                                修正
                                                            </Button>
                                                            <Button
                                                                variant="secondary"
                                                                onClick={() => handleNotifyDiscord(consultation.id)}
                                                            >
                                                                Discord投稿
                                                            </Button>
                                                        </td>
                                                    </tr>
                                                ))}
                                            </tbody>
                                        </table>
                                    </div>

                                    {/* ページネーション */}
                                    {consultations.last_page > 1 && (
                                        <div className="mt-4 flex justify-center">
                                            <nav className="flex space-x-2">
                                                {consultations.links.map((link, index) => (
                                                    <button
                                                        key={index}
                                                        onClick={() => {
                                                            if (link.url) {
                                                                router.visit(link.url);
                                                            }
                                                        }}
                                                        disabled={!link.url || link.active}
                                                        className={`px-3 py-2 rounded-md text-sm font-medium ${
                                                            link.active
                                                                ? 'bg-blue-600 text-white'
                                                                : link.url
                                                                ? 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-300'
                                                                : 'bg-gray-100 text-gray-400 cursor-not-allowed'
                                                        }`}
                                                        dangerouslySetInnerHTML={{ __html: link.label }}
                                                    />
                                                ))}
                                            </nav>
                                        </div>
                                    )}
                                </>
                            ) : (
                                <div className="text-center py-12">
                                    <p className="text-gray-500">相談履歴がありません</p>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>

            {/* 修正モーダル */}
            <Modal show={showModal} onClose={() => setShowModal(false)} title="回答を修正" maxWidth="2xl">
                {selectedConsultation && (
                    <form onSubmit={handleSubmitCorrection}>
                        <div className="mb-4">
                            <InputLabel htmlFor="original_question" value="質問" />
                            <div className="mt-1 p-3 bg-gray-50 rounded-md border border-gray-300">
                                <p className="text-sm text-gray-700 whitespace-pre-wrap">
                                    {selectedConsultation.question}
                                </p>
                            </div>
                        </div>

                        <div className="mb-4">
                            <InputLabel htmlFor="original_answer" value="元の回答" />
                            <div className="mt-1 p-3 bg-yellow-50 rounded-md border border-yellow-300 max-h-96 overflow-y-auto">
                                {(() => {
                                    const { summary, action, advice, detail } = splitAnswer(selectedConsultation.answer);
                                    
                                    return (
                                        <>
                                            {/* 要約セクション */}
                                            {summary && (
                                                <div className="mb-3">
                                                    <div className="flex items-center mb-1">
                                                        <span className="font-bold text-sm text-blue-600">💡 要約</span>
                                                    </div>
                                                    <div className="prose prose-sm max-w-none prose-headings:font-bold prose-h1:text-xl prose-h2:text-lg prose-h3:text-base prose-p:text-gray-700 prose-a:text-blue-600 prose-strong:text-gray-900">
                                                        <ReactMarkdown
                                                            remarkPlugins={[remarkGfm]}
                                                            components={{
                                                                code({node, inline, className, children, ...props}: any) {
                                                                    const match = /language-(\w+)/.exec(className || '');
                                                                    return !inline && match ? (
                                                                        <SyntaxHighlighter
                                                                            style={vscDarkPlus}
                                                                            language={match[1]}
                                                                            PreTag="div"
                                                                            {...props}
                                                                        >
                                                                            {String(children).replace(/\n$/, '')}
                                                                        </SyntaxHighlighter>
                                                                    ) : (
                                                                        <code className="bg-gray-100 px-1 py-0.5 rounded text-sm" {...props}>
                                                                            {children}
                                                                        </code>
                                                                    );
                                                                }
                                                            }}
                                                        >
                                                            {summary}
                                                        </ReactMarkdown>
                                                    </div>
                                                </div>
                                            )}
                                            
                                            {/* 今すぐやることセクション */}
                                            {action && (
                                                <div className="mb-3 bg-yellow-100 border-l-4 border-yellow-500 p-3 rounded">
                                                    <div className="flex items-center mb-1">
                                                        <span className="font-bold text-sm text-yellow-700">✅ 今すぐやること</span>
                                                    </div>
                                                    <div className="prose prose-sm max-w-none prose-headings:font-bold prose-h1:text-xl prose-h2:text-lg prose-h3:text-base prose-p:text-gray-700 prose-a:text-blue-600 prose-strong:text-gray-900">
                                                        <ReactMarkdown
                                                            remarkPlugins={[remarkGfm]}
                                                            components={{
                                                                code({node, inline, className, children, ...props}: any) {
                                                                    const match = /language-(\w+)/.exec(className || '');
                                                                    return !inline && match ? (
                                                                        <SyntaxHighlighter
                                                                            style={vscDarkPlus}
                                                                            language={match[1]}
                                                                            PreTag="div"
                                                                            {...props}
                                                                        >
                                                                            {String(children).replace(/\n$/, '')}
                                                                        </SyntaxHighlighter>
                                                                    ) : (
                                                                        <code className="bg-gray-100 px-1 py-0.5 rounded text-sm" {...props}>
                                                                            {children}
                                                                        </code>
                                                                    );
                                                                }
                                                            }}
                                                        >
                                                            {action}
                                                        </ReactMarkdown>
                                                    </div>
                                                </div>
                                            )}
                                            
                                            {/* アドバイスセクション */}
                                            {advice && (
                                                <div className="mb-3 bg-green-100 border-l-4 border-green-500 p-3 rounded">
                                                    <div className="flex items-center mb-1">
                                                        <span className="font-bold text-sm text-green-700">💬 アドバイス</span>
                                                    </div>
                                                    <div className="prose prose-sm max-w-none prose-headings:font-bold prose-h1:text-xl prose-h2:text-lg prose-h3:text-base prose-p:text-gray-700 prose-a:text-blue-600 prose-strong:text-gray-900">
                                                        <ReactMarkdown
                                                            remarkPlugins={[remarkGfm]}
                                                            components={{
                                                                code({node, inline, className, children, ...props}: any) {
                                                                    const match = /language-(\w+)/.exec(className || '');
                                                                    return !inline && match ? (
                                                                        <SyntaxHighlighter
                                                                            style={vscDarkPlus}
                                                                            language={match[1]}
                                                                            PreTag="div"
                                                                            {...props}
                                                                        >
                                                                            {String(children).replace(/\n$/, '')}
                                                                        </SyntaxHighlighter>
                                                                    ) : (
                                                                        <code className="bg-gray-100 px-1 py-0.5 rounded text-sm" {...props}>
                                                                            {children}
                                                                        </code>
                                                                    );
                                                                }
                                                            }}
                                                        >
                                                            {advice}
                                                        </ReactMarkdown>
                                                    </div>
                                                </div>
                                            )}
                                            
                                            {/* 詳細セクション - 折りたたみ可能 */}
                                            {detail && (
                                                <>
                                                    <button
                                                        type="button"
                                                        onClick={() => setExpandedDetail(!expandedDetail)}
                                                        className="text-blue-600 text-sm hover:underline mb-2 flex items-center"
                                                    >
                                                        {expandedDetail ? '詳細を閉じる ▲' : '詳しく見る ▼'}
                                                    </button>
                                                    
                                                    {expandedDetail && (
                                                        <div className="mt-2 pt-3 border-t border-gray-300">
                                                            <div className="flex items-center mb-1">
                                                                <span className="font-bold text-sm text-gray-600">📖 詳細</span>
                                                            </div>
                                                            <div className="prose prose-sm max-w-none prose-headings:font-bold prose-h1:text-xl prose-h2:text-lg prose-h3:text-base prose-p:text-gray-700 prose-a:text-blue-600 prose-strong:text-gray-900">
                                                                <ReactMarkdown
                                                                    remarkPlugins={[remarkGfm]}
                                                                    components={{
                                                                        code({node, inline, className, children, ...props}: any) {
                                                                            const match = /language-(\w+)/.exec(className || '');
                                                                            return !inline && match ? (
                                                                                <SyntaxHighlighter
                                                                                    style={vscDarkPlus}
                                                                                    language={match[1]}
                                                                                    PreTag="div"
                                                                                    {...props}
                                                                                >
                                                                                    {String(children).replace(/\n$/, '')}
                                                                                </SyntaxHighlighter>
                                                                            ) : (
                                                                                <code className="bg-gray-100 px-1 py-0.5 rounded text-sm" {...props}>
                                                                                    {children}
                                                                                </code>
                                                                            );
                                                                        }
                                                                    }}
                                                                >
                                                                    {detail}
                                                                </ReactMarkdown>
                                                            </div>
                                                        </div>
                                                    )}
                                                </>
                                            )}
                                            
                                            {/* フォールバック: 4段階構造がない場合は従来通り表示 */}
                                            {!summary && !action && !advice && !detail && (
                                                <div className="prose prose-sm max-w-none prose-headings:font-bold prose-h1:text-xl prose-h2:text-lg prose-h3:text-base prose-p:text-gray-700 prose-a:text-blue-600 prose-strong:text-gray-900">
                                                    <ReactMarkdown
                                                        remarkPlugins={[remarkGfm]}
                                                        components={{
                                                            code({node, inline, className, children, ...props}: any) {
                                                                const match = /language-(\w+)/.exec(className || '');
                                                                return !inline && match ? (
                                                                    <SyntaxHighlighter
                                                                        style={vscDarkPlus}
                                                                        language={match[1]}
                                                                        PreTag="div"
                                                                        {...props}
                                                                    >
                                                                        {String(children).replace(/\n$/, '')}
                                                                    </SyntaxHighlighter>
                                                                ) : (
                                                                    <code className="bg-gray-100 px-1 py-0.5 rounded text-sm" {...props}>
                                                                        {children}
                                                                    </code>
                                                                );
                                                            }
                                                        }}
                                                    >
                                                        {selectedConsultation.answer}
                                                    </ReactMarkdown>
                                                </div>
                                            )}
                                        </>
                                    );
                                })()}
                            </div>
                        </div>

                        <div className="mb-4">
                            <InputLabel htmlFor="correct_answer" value="正しい回答（必須）" />
                            <textarea
                                id="correct_answer"
                                className="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                rows={10}
                                value={data.correct_answer}
                                onChange={(e) => setData('correct_answer', e.target.value)}
                                required
                                placeholder="正しい回答を入力してください"
                            />
                            <InputError message={errors.correct_answer} className="mt-2" />
                        </div>

                        <div className="mb-4">
                            <InputLabel htmlFor="corrected_by" value="修正者名（任意）" />
                            <input
                                type="text"
                                id="corrected_by"
                                className="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                value={data.corrected_by}
                                onChange={(e) => setData('corrected_by', e.target.value)}
                                placeholder="修正者名を入力（空欄の場合はログインユーザー名）"
                            />
                            <InputError message={errors.corrected_by} className="mt-2" />
                        </div>

                        <div className="flex justify-end space-x-4">
                            <Button
                                type="button"
                                variant="secondary"
                                onClick={() => setShowModal(false)}
                            >
                                キャンセル
                            </Button>
                            <Button type="submit" disabled={processing}>
                                {processing ? '修正中...' : '修正する'}
                            </Button>
                        </div>
                    </form>
                )}
            </Modal>
        </AdminV2Layout>
    );
}

