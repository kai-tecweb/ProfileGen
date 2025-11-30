import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import { PageProps } from '@/Types';
import { Client, Proposal } from '@/Types';
import { route } from 'ziggy-js';
import Button from '@/Components/Button';
import Modal from '@/Components/Modal';
import LoadingSpinner from '@/Components/LoadingSpinner';
import { useState } from 'react';

interface ClientsShowProps extends PageProps {
    client: Client & {
        proposals?: Proposal[];
    };
}

export default function Show({ client }: ClientsShowProps) {
    const [showQuestionnaire, setShowQuestionnaire] = useState(false);
    const [showGenerateModal, setShowGenerateModal] = useState(false);
    const [isGenerating, setIsGenerating] = useState(false);

    const handleGenerateProposal = () => {
        if (!client.answers_text) {
            alert('ヒアリング回答が未入力です。先にクライアント情報を編集してヒアリング回答を入力してください。');
            return;
        }
        setShowGenerateModal(true);
    };

    const confirmGenerate = () => {
        setShowGenerateModal(false);
        setIsGenerating(true);
        router.post(
            route('clients.proposals.generate', client.id),
            {},
            {
                onSuccess: () => {
                    setIsGenerating(false);
                },
                onError: () => {
                    setIsGenerating(false);
                },
            }
        );
    };

    return (
        <AuthenticatedLayout
            header={
                <div className="flex justify-between items-center">
                    <h2 className="text-xl font-semibold leading-tight text-gray-800">
                        {client.name}様の詳細
                    </h2>
                    <div className="space-x-2">
                        <Link href={route('clients.edit', client.id)}>
                            <Button variant="secondary">編集</Button>
                        </Link>
                        <Button
                            variant="danger"
                            onClick={() => {
                                if (confirm('このクライアントを削除しますか？関連する提案もすべて削除されます。')) {
                                    router.delete(route('clients.destroy', client.id));
                                }
                            }}
                        >
                            削除
                        </Button>
                    </div>
                </div>
            }
        >
            <Head title={`${client.name}様の詳細`} />

            <Modal show={showGenerateModal} onClose={() => setShowGenerateModal(false)} title="提案生成の確認">
                <div className="space-y-4">
                    <p>提案を生成しますか？</p>
                    <div className="flex justify-end space-x-2">
                        <Button variant="secondary" onClick={() => setShowGenerateModal(false)}>
                            キャンセル
                        </Button>
                        <Button onClick={confirmGenerate}>生成</Button>
                    </div>
                </div>
            </Modal>

            {isGenerating && (
                <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                    <div className="bg-white rounded-lg p-8">
                        <LoadingSpinner message="提案を生成しています..." />
                    </div>
                </div>
            )}

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="space-y-6">
                        {/* クライアント情報 */}
                        <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div className="p-6">
                                <h3 className="text-lg font-semibold text-gray-900 mb-4">基本情報</h3>
                                <dl className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    <div>
                                        <dt className="text-sm font-medium text-gray-500">名前</dt>
                                        <dd className="mt-1 text-sm text-gray-900">{client.name}</dd>
                                    </div>
                                    <div>
                                        <dt className="text-sm font-medium text-gray-500">会社名</dt>
                                        <dd className="mt-1 text-sm text-gray-900">{client.company || '-'}</dd>
                                    </div>
                                </dl>
                                {client.memo && (
                                    <div className="mt-4">
                                        <dt className="text-sm font-medium text-gray-500">クライアント情報・特徴</dt>
                                        <dd className="mt-1 text-sm text-gray-900 whitespace-pre-wrap">{client.memo}</dd>
                                    </div>
                                )}
                                <div className="mt-6 flex justify-center">
                                    <Button
                                        onClick={handleGenerateProposal}
                                        disabled={!client.answers_text}
                                        className="px-8 py-3 text-lg"
                                    >
                                        提案生成
                                    </Button>
                                </div>
                                {!client.answers_text && (
                                    <p className="mt-2 text-sm text-red-600 text-center">
                                        ヒアリング回答を入力してください
                                    </p>
                                )}
                            </div>
                        </div>

                        {/* ヒアリング回答 */}
                        <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div className="p-6">
                                <div className="flex justify-between items-center mb-4">
                                    <h3 className="text-lg font-semibold text-gray-900">ヒアリング回答</h3>
                                    {client.questionnaire_text && (
                                        <button
                                            onClick={() => setShowQuestionnaire(!showQuestionnaire)}
                                            className="text-sm text-blue-600 hover:text-blue-800"
                                        >
                                            {showQuestionnaire ? '質問を非表示' : '送付した質問を表示'}
                                        </button>
                                    )}
                                </div>

                                {showQuestionnaire && client.questionnaire_text && (
                                    <div className="mb-4 p-4 bg-gray-50 rounded-md">
                                        <h4 className="text-sm font-medium text-gray-700 mb-2">送付した質問</h4>
                                        <pre className="text-sm text-gray-600 whitespace-pre-wrap font-mono">
                                            {client.questionnaire_text}
                                        </pre>
                                    </div>
                                )}

                                {client.answers_text ? (
                                    <div className="p-4 bg-gray-50 rounded-md">
                                        <pre className="text-sm text-gray-900 whitespace-pre-wrap">
                                            {client.answers_text}
                                        </pre>
                                    </div>
                                ) : (
                                    <p className="text-gray-500">ヒアリング回答が入力されていません。</p>
                                )}
                            </div>
                        </div>

                        {/* 提案一覧 */}
                        <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div className="p-6">
                                <h3 className="text-lg font-semibold text-gray-900 mb-4">提案履歴</h3>
                                {client.proposals && client.proposals.length > 0 ? (
                                    <div className="overflow-x-auto">
                                        <table className="min-w-full divide-y divide-gray-200">
                                            <thead className="bg-gray-50">
                                                <tr>
                                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        生成日時
                                                    </th>
                                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        操作
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody className="bg-white divide-y divide-gray-200">
                                                {client.proposals.map((proposal) => (
                                                    <tr key={proposal.id}>
                                                        <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                            {new Date(proposal.created_at).toLocaleString('ja-JP')}
                                                        </td>
                                                        <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                            <Link
                                                                href={route('proposals.show', proposal.id)}
                                                                className="text-blue-600 hover:text-blue-900 mr-4"
                                                            >
                                                                詳細
                                                            </Link>
                                                            <button
                                                                onClick={() => {
                                                                    if (confirm('この提案を削除してもよろしいですか？')) {
                                                                        router.delete(route('proposals.destroy', proposal.id));
                                                                    }
                                                                }}
                                                                className="text-red-600 hover:text-red-900"
                                                            >
                                                                削除
                                                            </button>
                                                        </td>
                                                    </tr>
                                                ))}
                                            </tbody>
                                        </table>
                                    </div>
                                ) : (
                                    <p className="text-gray-500">提案はまだありません。</p>
                                )}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

