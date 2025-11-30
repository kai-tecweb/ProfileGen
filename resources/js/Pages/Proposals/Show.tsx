import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import { PageProps } from '@/Types';
import { Proposal } from '@/Types';
import { route } from 'ziggy-js';
import Button from '@/Components/Button';

interface ProposalsShowProps extends PageProps {
    proposal: Proposal & {
        client?: {
            id: number;
            name: string;
        };
    };
}

export default function Show({ proposal }: ProposalsShowProps) {
    const handleDelete = () => {
        if (confirm('この提案を削除してもよろしいですか？')) {
            router.delete(route('proposals.destroy', proposal.id));
        }
    };

    const copyToClipboard = (text: string, label: string) => {
        navigator.clipboard.writeText(text).then(() => {
            alert(`${label}をクリップボードにコピーしました`);
        });
    };

    return (
        <AuthenticatedLayout
            header={
                <div className="flex justify-between items-center">
                    <h2 className="text-xl font-semibold leading-tight text-gray-800">
                        提案詳細
                    </h2>
                    <div className="space-x-2">
                        <Link href={route('proposals.edit', proposal.id)}>
                            <Button variant="secondary">編集</Button>
                        </Link>
                        <Button variant="danger" onClick={handleDelete}>
                            削除
                        </Button>
                    </div>
                </div>
            }
        >
            <Head title={`提案詳細: ${proposal.client?.name || ''}様`} />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="space-y-6">
                        {proposal.client && (
                            <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                                <div className="p-6">
                                    <div className="flex items-center justify-between">
                                        <div>
                                            <h3 className="text-lg font-semibold text-gray-900">
                                                {proposal.client.name}様
                                            </h3>
                                        </div>
                                        <Link
                                            href={route('clients.show', proposal.client.id)}
                                            className="text-blue-600 hover:text-blue-800"
                                        >
                                            クライアント詳細へ →
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        )}

                        <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div className="p-6">
                                <div className="flex justify-between items-center mb-4">
                                    <h3 className="text-lg font-semibold text-gray-900">X用プロフィール（160文字）</h3>
                                    <Button
                                        variant="secondary"
                                        onClick={() => copyToClipboard(proposal.x_profile, 'X用プロフィール')}
                                        className="text-sm"
                                    >
                                        コピー
                                    </Button>
                                </div>
                                <div className="p-4 bg-gray-50 rounded-md">
                                    <p className="text-sm text-gray-900 whitespace-pre-wrap">
                                        {proposal.x_profile}
                                    </p>
                                </div>
                                <p className="text-xs text-gray-500 mt-2">
                                    文字数: {proposal.x_profile.length}文字
                                </p>
                            </div>
                        </div>

                        <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div className="p-6">
                                <div className="flex justify-between items-center mb-4">
                                    <h3 className="text-lg font-semibold text-gray-900">Instagram用プロフィール（150文字）</h3>
                                    <Button
                                        variant="secondary"
                                        onClick={() => copyToClipboard(proposal.instagram_profile, 'Instagram用プロフィール')}
                                        className="text-sm"
                                    >
                                        コピー
                                    </Button>
                                </div>
                                <div className="p-4 bg-gray-50 rounded-md">
                                    <p className="text-sm text-gray-900 whitespace-pre-wrap">
                                        {proposal.instagram_profile}
                                    </p>
                                </div>
                                <p className="text-xs text-gray-500 mt-2">
                                    文字数: {proposal.instagram_profile.length}文字
                                </p>
                            </div>
                        </div>

                        <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div className="p-6">
                                <div className="flex justify-between items-center mb-4">
                                    <h3 className="text-lg font-semibold text-gray-900">ココナラ用プロフィール</h3>
                                    <Button
                                        variant="secondary"
                                        onClick={() => copyToClipboard(proposal.coconala_profile, 'ココナラ用プロフィール')}
                                        className="text-sm"
                                    >
                                        コピー
                                    </Button>
                                </div>
                                <div className="p-4 bg-gray-50 rounded-md">
                                    <p className="text-sm text-gray-900 whitespace-pre-wrap">
                                        {proposal.coconala_profile}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div className="p-6">
                                <div className="flex justify-between items-center mb-4">
                                    <h3 className="text-lg font-semibold text-gray-900">商品設計案</h3>
                                    <Button
                                        variant="secondary"
                                        onClick={() => copyToClipboard(proposal.product_design, '商品設計案')}
                                        className="text-sm"
                                    >
                                        コピー
                                    </Button>
                                </div>
                                <div className="p-4 bg-gray-50 rounded-md">
                                    <p className="text-sm text-gray-900 whitespace-pre-wrap">
                                        {proposal.product_design}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

