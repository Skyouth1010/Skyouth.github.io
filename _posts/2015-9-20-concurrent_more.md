---
layout: post

title: JAVA系列之－－并发编程（进阶）
subtitle: "CAS理论、JUC包等"
cover_image: blog-cover.jpg

author:
  name: Skyouth
  weibo: skyouth
  bio: Runnable,cmbchina
  
tags: [技术]
---
#ReentrantLock 、synchronized、volatile
1. Synchronized和ReentrantLock的比较	* Synchronized：		+ 同步不能中断		+ 当 JVM 用 synchronized 管理锁定请求和释放时，JVM 在生成线程转储时能够包括锁定信息		+ 内置锁为不公平锁（公平锁使线程按照请求锁的顺序依次获得锁；而不公平锁则允许直接获取锁）	* ReentrantLock：		+ 必须在finally里手工unlock		+ ReentrantLock默认也是非公平锁，但他的构造器有一个fair参数，可以设置为公平锁		+ 同时ReentrantLock有以下一些机制：			- 可轮询锁：解决死锁，不能同时获得两个锁时，不会阻塞			- 可定时锁：tryLock(long time, TimeUnit unit)，阻塞指定时间未获得锁，则退出			- 可中断锁：lock.lockInterruptibly()，但线程被interrupt时，lock会抛出InterruptedException 			- 公平队列：			- 非块结构的锁：	
	公平锁与非公平锁：通常非公平锁性能更高，公平性将由于在挂起线程和恢复线程时存在的开销而极大地降低性能。2. synchronized和volatile区别	加锁机制可以确保可见性和原子性，但是volatile只能确保可见性。三种适合使用volatile的场景：		* 对变量的写入操作不依赖变量的当前值，或者你能确保只有单个线程更新变量的值；		* 该变量不会与其他状态变量一起纳入不变性条件中；		* 在访问变量时不需要加锁	线程安全性：可见性、原子性、有序性		线程活跃性问题：死锁、饥饿、活锁		性能问题：		可重入锁：3. 线程的可见性	当一个变量的读操作和写操作不在一个线程里进行时，如果不使用同步机制，写操作的结果可能无法让读操作看到。避免可见性问题的方法有同步或者volatile。4.	线程池，怎么判断当前有空线程来执行?5.	死锁	* 锁顺序死锁		线程a获取a1的锁，然后尝试获取b1的锁；而线程b获取b1的锁，尝试获取a1的锁，这样就容易发生锁顺序死锁，解决办法是所有线程以固定的顺序来获取锁。或者使用可轮询锁trylock	* 动态锁顺序死锁		
		比如一个转账程序，a账号转账给b账号的方法，。。		具体参考《并发编程实践》10.1.2	* 如果持有锁的情况下调用某个外部的方法，那么需要警惕死锁，因为外部方法也可能持有某个锁，这样就也可能产生锁顺序死锁。所以应该尽量使用开放调用（调用某个方式时不需要持有锁），这样更有利于进行死锁分析。	* 资源死锁，比如数据库连接池6.	活锁	线程不会阻塞，但是会重复执行相同的操作，而不能继续执行后续的处理，通过等待随机长度的时间和回退可以有效地避免活锁的发生。(没遇到过这种场景。。不是很懂)#CAS（Compare and Set）
<a href='http://blog.csdn.net/hsuxu/article/details/9467651'>JAVA CAS原理深度分析</a><a href='http://blog.csdn.net/aesop_wubo/article/details/7537960'>JAVA并发编程学习笔记之CAS操作</a>非阻塞同步，将当前值和预期值作比较，相等则以原子方式将变量设置为给定的更新值。这是一种非阻塞算法的思想。一般为了解决共享变量的冲突问题，会采用同步或加锁的机制，其实这是一种悲观锁，即开始操作前就加锁，而cas就是一种乐观锁，在更新前先进行比较操作，相等再做更新。Cas操作具有volatile读和写的内存语义。但是cas存在的几个问题：       * aba问题；   * 不断的循环，如果一直不能成功，cpu做无用功，开销大；   * 只能保证一个共享变量的原子操作If I need atomic access to an int field inside an object, is it sufficient to declare the field as an AtomicInteger or do I need to use an AtomicIntegerFieldUpdater? (and why?)Using an AtomicInteger is sufficient. Atomic updaters are for use with volatile fields; the primary use case is data structures which have large numbers of fields that require atomic access; you use the field updater to use those fields with atomic semantics without having an AtomicInteger reference for each field.<a href='http://www.javamex.com/tutorials/synchronization_concurrency_7_atomic_updaters.shtml'>The Atomic classes in Java:atomic field updaters</a>摘要：Atomic field updaters are generally used when one or both of the following are true:You generally want to refer to the variable "normally" (that is, without having to always refer to it via the get or set methods on the atomic classes) but occasionally need an atomic get-set operation (which you can't do with a normal volatile field);you are going to create a large number of objects of the given type, and don't want every single instance to have to have an extra object embedded in it just for atomic access.我的理解：AtomicInteger支持原子性和可见性，而AtomicIntegerFieldUpdater是针对volatile变量使用的，当这种变量大部分情况都只是读取，偶尔需要原子读写操作，可以使用AtomicIntegerFieldUpdater。这样就不需要使用AtomicInteger了，提高性能。#JUC包
<img src='/images/juc.png'>
* Juc的实现	通用化的实现模式：	首先，声明共享变量为volatile；然后，使用CAS的原子条件更新来实现线程之间的同步；同时，配合以volatile的读／写和CAS所具有的volatile读和写的内存语义来实现线程之间的通信。AQS，非阻塞数据结构，原子变量类，这些concurrent包中的基础类都是使用这种模式来实现的，而concurrent包中的高层类又是依赖于这些基础类来实现的。从整体来看，concurrent包的实现示意图如下：
<img src='/images/concurrent.png'>

* 实际使用juc（java.util.concurrent包）的场景回顾		
	+ xpad中，在处理wms文件时，wms文件有四个，但是最终的结果要根据四个文件的数据来组合，这是就可以使用四个线程处理这个四个文件，同时使用闭锁使这四个线程都处理完成后，再处理后面的组合操作。	+ 线程池，线程池中的阻塞队列，使用arrayblockingqueue	+ futuretask，异步线程处理，同时要处理其他操作，完成后再通过get方法等待futuretask的处理结果* java内存模型和happens－before	一个线程中的各个操作之间如果不存在数据流依赖性，这些操作就可以乱序执行。如：
	<pre><code>a＝1；x＝b；</code></pre>
	两条语句之间不存在数据流依赖，那么第二条语句可以先执行。而同步可以限制这种重排序问题。一个操作happens-before另一个操作是指一个操作的结果对另一个操作是可见的。